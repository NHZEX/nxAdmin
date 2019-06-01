<?php
/**
 * Created by PhpStorm.
 * User: NHZEXG
 * Date: 2018/9/14
 * Time: 10:40
 */

namespace app\command;

use HZEX\Util;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
use think\Db;
use think\Exception;
use think\facade\App;

/**
 * 批量创建数据结构到模型
 * Class CreateModel
 * @package app\command
 */
class CreateModel extends Command
{
    const FILTE_TABLE = ["_phinxlog"];

    public function configure()
    {
        $this->setName('model:create')
            ->addOption('print', 'p', Option::VALUE_NONE, '打印')
            ->addArgument('table', Argument::OPTIONAL, '指定表');
    }

    /**
     * @param Input  $input
     * @param Output $output
     * @return int|void|null
     * @throws Exception
     */
    public function execute(Input $input, Output $output)
    {
        $is_print = (bool) $input->getOption('print');

        $output_align = 22;
        // 导出配置
        $model_path = 'model';

        // 初始化
        $build_date = date('Y/m/d');
        $build_time = date('H:i');
        $export_path = realpath(App::getAppPath() . $model_path);
        $namespace_path = 'app\\' . $model_path;

        // 加载数据
        $config = Db::connect()->getConfig();
        $database = $config['database'];

        /** @noinspection SqlNoDataSourceInspection SqlDialectInspection SqlResolve */
        $sql = "select * from information_schema.tables where TABLE_SCHEMA='{$database}' and TABLE_TYPE='BASE TABLE'";
        $tables = Db::query($sql);
        $table_names = array_column($tables, 'TABLE_COMMENT', 'TABLE_NAME');
        $existsModels = scandir(App::getAppPath() . 'model' . DIRECTORY_SEPARATOR);

        // 指定导出表
        $need_table = $input->getArgument('table');
        $need_table = $need_table ? explode(',', $need_table) : array_keys($table_names);

        foreach ($table_names as $table_name => $table_comment) {
            $model_table_name = $table_name;
            $class_name = Util::toUpperCamelCase($model_table_name);

            $output->write(
                '<info>'
                . str_pad($table_name, $output_align) . " => " . str_pad($class_name, $output_align)
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

            /** @noinspection SqlResolve SqlNoDataSourceInspection SqlDialectInspection */
            $sql = "select * from information_schema.COLUMNS "
                . "where table_name = '{$table_name}' and table_schema = '{$database}'";
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
            $class_text .= "namespace {$namespace_path};\n";
            $class_text .= "\n";
            $class_text .= "/**\n";
            $class_text .= " * Model: {$table_comment}\n";
            $class_text .= " * Class {$class_name}\n";
            $class_text .= " * @package {$namespace_path}\n";
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
            $class_text .= "class {$class_name} extends Base\n";
            $class_text .= "{\n";
            $class_text .= "    protected \$table = '{$model_table_name}';\n";
            $class_text .= "    protected \$pk = '{$pk_field_name}';\n";
            $class_text .= "    \n";
            $class_text .= "}\n";

            $file_name = $export_path . DIRECTORY_SEPARATOR . "{$class_name}.php";
            if ($is_print) {
                $output->warning(">> output: {$file_name}");
                // $class_text = preg_replace('~^(.+)$~m', "    $1", $class_text);
                $output->writeln($class_text);
            } else {
                file_put_contents($file_name, $class_text);
            }
        }
    }
}
