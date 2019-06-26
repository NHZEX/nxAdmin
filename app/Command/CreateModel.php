<?php
/**
 * Created by PhpStorm.
 * User: NHZEXG
 * Date: 2018/9/14
 * Time: 10:40
 */

namespace app\Command;

use HZEX\Util;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
use think\facade\Db;

/**
 * 批量创建数据结构到模型
 * Class CreateModel
 * @package app\command
 */
class CreateModel extends Command
{
    const FILTE_TABLE = ["_phinxlog"];

    const OUTPUT_ALIGN = 22;

    public function configure()
    {
        $this->setName('model:create')
            ->addOption('connect', 'c', Option::VALUE_OPTIONAL, '指定连接', '')
            ->addOption('dir', 'd', Option::VALUE_OPTIONAL, '模型目录', './app/Model')
            ->addOption('namespace', 'a', Option::VALUE_OPTIONAL, '命名空间', 'app\\Model')
            ->addOption('print', 'p', Option::VALUE_NONE, '打印')
            ->addArgument('table', Argument::OPTIONAL, '指定表');
    }

    /**
     * @param Input  $input
     * @param Output $output
     * @return int|void|null
     */
    public function execute(Input $input, Output $output)
    {
        $out_print = (bool) $input->getOption('print');
        $connect = $input->getOption('connect');
        $namespace = $input->getOption('namespace');
        $out_dir = $input->getOption('dir');

        // 初始化
        $export_path = realpath($out_dir);

        if (!is_string($export_path) || !is_dir($export_path)) {
            $this->output->warning("模型导出目录不存在: {$out_dir}");
            return;
        }
        // 自动填充最后一个目录分割符
        if (strlen($export_path) - 1 !== strrpos($export_path, DIRECTORY_SEPARATOR)) {
            $export_path .= DIRECTORY_SEPARATOR;
        }

        $output->info("DbConnect：\t{$connect}");
        $output->info("ModelDir：\t{$export_path}");
        $output->info("Namespace:\t{$namespace}");
        $output->info("=========================================================");

        Db::connect($connect);

        // 获取数据库信息
        $config = Db::getConfig();
        $database = $config['database'];

        // 加载数据
        /** @noinspection SqlNoDataSourceInspection SqlDialectInspection SqlResolve */
        $sql = "select * from information_schema.tables where TABLE_SCHEMA='{$database}' and TABLE_TYPE='BASE TABLE'";
        $tables = Db::query($sql);
        $table_names = array_column($tables, 'TABLE_COMMENT', 'TABLE_NAME');
        $existsModels = scandir($export_path);

        // 指定导出表
        $need_table = $input->getArgument('table');
        $need_table = $need_table ? explode(',', $need_table) : array_keys($table_names);

        foreach ($table_names as $table_name => $table_comment) {
            $model_table_name = $table_name;
            $class_name = Util::toUpperCamelCase($model_table_name);

            $output->write(
                '<info>'
                . str_pad($table_name, self::OUTPUT_ALIGN) . " => " . str_pad($class_name, self::OUTPUT_ALIGN)
                . ' </info>'
            );

            // 过滤 && 不重复生成模型
            if (in_array($table_name, self::FILTE_TABLE)
                || !in_array($table_name, $need_table)
                || in_array("{$class_name}.php", $existsModels)
            ) {
                $output->info('Skip');
                continue;
            }

            $output->warning('Create');

            $class_text = $this->createModel($database, $table_name, $table_comment, $class_name, $namespace);

            $file_name = $export_path . "{$class_name}.php";

            if ($out_print) {
                $output->warning(">> output: {$file_name}");
                // $class_text = preg_replace('~^(.+)$~m', "    $1", $class_text);
                $output->writeln($class_text);
            } else {
                file_put_contents($file_name, $class_text);
            }
        }
    }

    private function createModel(
        string $database,
        string $tableName,
        string $tableComment,
        string $className,
        string $namespaces
    ): string {
        $build_date = date('Y/m/d');
        $build_time = date('H:i');

        /** @noinspection SqlResolve SqlNoDataSourceInspection SqlDialectInspection */
        $sql = "select * from information_schema.COLUMNS "
            . "where table_name = '{$tableName}' and table_schema = '{$database}'";
        $table_fields = Db::query($sql);

        $pk_field_name = '';

        $class_text = "<?php\n";
        $class_text .= "/**\n";
        $class_text .= " * Created by Automatic build\n";
        $class_text .= " * User: System\n";
        $class_text .= " * Date: {$build_date}\n";
        $class_text .= " * Time: {$build_time}\n";
        $class_text .= " */\n";
        $class_text .= "\n";
        $class_text .= "namespace {$namespaces};\n";
        $class_text .= "\n";
        $class_text .= "/**\n";
        $class_text .= " * Model: {$tableComment}\n";
        $class_text .= " * Class {$className}\n";
        $class_text .= " * @package {$namespaces}\n";
        $class_text .= " *\n";

        $comment_arr = [];
        $max_type_len = 0;
        $max_field_len = 0;
        foreach ($table_fields as $value) {
            $field_name = $value['COLUMN_NAME'];
            $comment = $value['COLUMN_COMMENT'];
            $comment = empty($comment) ? '' : (' ' . $comment);

            if ('PRI' === $value['COLUMN_KEY']) {
                $pk_field_name = $field_name;
            }
            switch ($value['DATA_TYPE']) {
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

            $max_type_len = max($max_type_len, strlen($type));
            $max_field_len = max($max_field_len, strlen($field_name));
            $comment_arr[] = [$type, '$' . $field_name, $comment];
        }

        foreach ($comment_arr as $value) {
            [$type, $field_name, $comment] = $value;

            $type = str_pad($type, $max_type_len + 1);
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