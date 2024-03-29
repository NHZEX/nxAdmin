<?php

namespace app\Command;

use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
use think\db\ConnectionInterface;
use think\db\PDOConnection;
use think\facade\Db;
use Zxin\Util;
use function array_column;
use function count;
use function explode;
use function file_put_contents;
use function in_array;
use function is_dir;
use function is_string;
use function max;
use function realpath;
use function scandir;
use function str_pad;
use function strlen;
use function strpos;
use function strrpos;
use function trim;

/**
 * 批量创建数据结构到模型
 */
class CreateModel extends Command
{
    public const FILTE_TABLE = ['_phinxlog', 'activity_log'];

    public const OUTPUT_ALIGN = 22;

    public function configure()
    {
        $this->setName('model:create')
            ->addOption('connect', 'c', Option::VALUE_OPTIONAL, '指定连接', '')
            ->addOption('dir', 'd', Option::VALUE_OPTIONAL, '模型目录', './app/Model')
            ->addOption('namespace', 'a', Option::VALUE_OPTIONAL, '命名空间', 'app\\Model')
            ->addOption('print', 'p', Option::VALUE_NONE, '打印')
            ->addOption('save', 's', Option::VALUE_NONE, '保存（只能和指定表同时使用, 且与打印互斥）')
            ->addArgument('table', Argument::OPTIONAL, '指定表');
    }

    /**
     * @param Input  $input
     * @param Output $output
     * @return int
     */
    public function execute(Input $input, Output $output): int
    {
        $out_print = (bool) $input->getOption('print');
        $save      = (bool) $input->getOption('save');
        $connect   = $input->getOption('connect');
        $namespace = $input->getOption('namespace');
        $out_dir   = $input->getOption('dir');

        // 初始化
        $export_path = realpath($out_dir);

        if (!is_string($export_path) || !is_dir($export_path)) {
            $this->output->warning("模型导出目录不存在: {$out_dir}");
            return 1;
        }
        // 自动填充最后一个目录分割符
        if (strlen($export_path) - 1 !== strrpos($export_path, DIRECTORY_SEPARATOR)) {
            $export_path .= DIRECTORY_SEPARATOR;
        }

        $output->info("DbConnect：\t{$connect}");
        $output->info("ModelDir：\t{$export_path}");
        $output->info("Namespace:\t{$namespace}");
        $output->info("=========================================================");

        /** @var PDOConnection $db */
        $db       = Db::connect($connect);
        $database = $db->getConfig()['database'];

        // 加载数据
        /** @noinspection SqlNoDataSourceInspection SqlDialectInspection */
        $sql          = "select * from information_schema.tables where TABLE_SCHEMA='{$database}' and TABLE_TYPE='BASE TABLE'";
        $tables       = $db->query($sql);
        $table_names  = array_column($tables, 'TABLE_COMMENT', 'TABLE_NAME');
        $existsModels = scandir($export_path);

        // 指定导出表
        $need_table = $input->getArgument('table');
        $is_need    = !empty($need_table);
        $need_table = explode(',', $need_table);
        if ($is_need) {
            foreach ($need_table as &$value) {
                $name_hits = [];
                foreach ($table_names as $table_name => $comment) {
                    if (0 === strpos($table_name, $value)) {
                        $name_hits[] = $table_name;
                    }
                }
                if (count($name_hits) === 1) {
                    $value = $name_hits[0];
                } elseif (count($name_hits) > 1) {
                    $value = $this->output->choice($this->input, "输入的表名（{$value}）可能是如下匹配: ", $name_hits, null);
                } else {
                    $output->error("输入的表名无法满足如何匹配: {$value}");
                    return 1;
                }
            }
        }

        // 如果指定表，默认屏幕输出
        if ($is_need && !$save) {
            $out_print = true;
        }

        foreach ($table_names as $table_name => $table_comment) {
            $model_table_name = $table_name;
            $class_name       = Util::toUpperCamelCase($model_table_name);

            $output->write(
                '<info>'
                . str_pad($table_name, self::OUTPUT_ALIGN) . " => " . str_pad($class_name, self::OUTPUT_ALIGN)
                . ' </info>'
            );

            // 过滤 && 不重复生成模型
            if (in_array($table_name, self::FILTE_TABLE)) {
                $output->error('Ignore');
                continue;
            }
            if (!$out_print && (in_array("{$class_name}.php", $existsModels) || in_array("{$class_name}Model.php", $existsModels))) {
                $output->error('Exist');
                continue;
            }
            if ($is_need && !in_array($table_name, $need_table)) {
                $output->info('Skip');
                continue;
            }

            $output->warning('Create');

            $class_name = "{$class_name}Model";

            $class_text = $this->createModel($db, $database, $table_name, trim($table_comment), $class_name, $namespace);

            $file_name = $export_path . "{$class_name}.php";

            if ($out_print) {
                $output->warning(">> output: {$file_name}");
                // $class_text = preg_replace('~^(.+)$~m', "    $1", $class_text);
                $output->writeln($class_text);
            } else {
                file_put_contents($file_name, $class_text);
            }
        }

        return 0;
    }

    private function createModel(
        ConnectionInterface $db,
        string              $database,
        string              $tableName,
        string              $tableComment,
        string              $className,
        string              $namespaces
    ): string {
        /** @noinspection SqlNoDataSourceInspection SqlDialectInspection */
        $sql          = "select * from information_schema.COLUMNS "
            . "where `table_name` = '{$tableName}' and `table_schema` = '{$database}' order by `ORDINAL_POSITION` ASC";
        $table_fields = $db->query($sql);

        $pk_field_name = '';

        $class_text = "<?php\n";
        $class_text .= "\n";
        $class_text .= "namespace {$namespaces};\n";
        $class_text .= "\n";
        $class_text .= "/**\n";
        if (!empty($tableComment)) {
            $class_text .= " * model: {$tableComment}\n";
        }

        $comment_arr   = [];
        $max_type_len  = 0;
        $max_field_len = 0;
        foreach ($table_fields as $value) {
            $field_name = $value['COLUMN_NAME'];
            $comment    = $value['COLUMN_COMMENT'];
            $comment    = empty($comment) ? '' : (' ' . $comment);

            if ('PRI' === $value['COLUMN_KEY']) {
                $pk_field_name = $field_name;
            }
            switch ($value['DATA_TYPE']) {
                case 'bit':
                case 'int':
                case 'bigint':
                case 'integer':
                case 'tinyint':
                case 'mediumint':
                    $type = 'int';
                    break;
                case 'double':
                case 'float':
                    $type = 'float';
                    break;
                case 'decimal':
                case 'enum':
                case 'text':
                case 'varchar':
                case 'tinytext':
                case 'binary':
                case 'varbinary':
                    $type = 'string';
                    break;
                case 'json':
                    $type = 'array';
                    break;
                default:
                    $type = 'mixed';
            }

            $max_type_len  = max($max_type_len, strlen($type));
            $max_field_len = max($max_field_len, strlen($field_name));
            $comment_arr[] = [$type, '$' . $field_name, $comment];
        }

        foreach ($comment_arr as $value) {
            [$type, $field_name, $comment] = $value;

            $type       = str_pad($type, $max_type_len + 1);
            $field_name = empty($comment) ? $field_name : str_pad($field_name, $max_field_len + 1);

            $class_text .= " * @property {$type}{$field_name}{$comment}\n";
        }

        $class_text .= " */\n";
        $class_text .= "class {$className} extends Base\n";
        $class_text .= "{\n";
        $class_text .= "    protected \$table = '{$tableName}';\n";
        $class_text .= "    protected \$pk = '{$pk_field_name}';\n";
        $class_text .= "    \n";
        $class_text .= "}\n";

        return $class_text;
    }
}
