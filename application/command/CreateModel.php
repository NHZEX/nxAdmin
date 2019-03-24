<?php
/**
 * Created by PhpStorm.
 * User: NHZEXG
 * Date: 2018/9/14
 * Time: 10:40
 */

namespace app\command;

use basis\Util;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\Db;
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
        $this->setName('create_model');
    }

    /**
     * @param Input  $input
     * @param Output $output
     * @return int|void|null
     * @throws \think\Exception
     */
    public function execute(Input $input, Output $output)
    {
        // 导出配置
        $model_path = 'model';

        // 初始化
        $build_date = date('Y/m/d');
        $build_time = date('H:i');
        $export_path = realpath(App::getAppPath() . $model_path);
        $namespace_path = 'app\\'.$model_path;

        // 加载数据
        $config = Db::connect()->getConfig();
        $database = $config['database'];

        /** @noinspection SqlNoDataSourceInspection SqlDialectInspection SqlResolve */
        $sql = "select * from information_schema.tables where TABLE_SCHEMA='{$database}' and TABLE_TYPE='BASE TABLE'";
        $tables = Db::query($sql);
        $table_names = array_column($tables, 'TABLE_COMMENT', 'TABLE_NAME');
        $existsModels = scandir(App::getAppPath() . 'model' . DIRECTORY_SEPARATOR);

        foreach ($table_names as $table_name => $table_comment) {
            //过滤
            if (in_array($table_name, self::FILTE_TABLE)) {
                continue;
            }

            /** @noinspection SqlResolve SqlNoDataSourceInspection SqlDialectInspection */
            $sql = "select * from information_schema.COLUMNS "
                . "where table_name = '{$table_name}' and table_schema = '{$database}'";
            $table_fields = Db::query($sql);

            $model_table_name = $table_name;
            $class_name = Util::toUpperCamelCase($model_table_name);

            //不重复生成模型
            if (in_array("{$class_name}.php", $existsModels)) {
                continue;
            }

            $pk_field_name = '';

            $class_text = "<?php\n";
            $class_text .= "/**\n";
            $class_text .= " * Created by Automatic build\n";
            $class_text .= " * User: Auooru\n";
            $class_text .= " * Date: {$build_date}\n";
            $class_text .= " * Time: {$build_time}\n";
            $class_text .= " */\n";
            $class_text .= "\n";
            $class_text .= "namespace {$namespace_path};\n";
            $class_text .= "\n";
            $class_text .= "/**\n";
            $class_text .= " * {$table_comment}\n";
            $class_text .= " * Class {$class_name}\n";
            $class_text .= " * @package {$namespace_path}\n";
            $class_text .= " *\n";
            foreach ($table_fields as $value) {
                $field_name = $value['COLUMN_NAME'];
                $comment = $value['COLUMN_COMMENT'];

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

                $class_text .= " * @property {$type} $"."{$field_name} {$comment}\n";
            }

            $class_text .= " */\n";
            $class_text .= "class {$class_name} extends Base\n";
            $class_text .= "{\n";
            $class_text .= "    protected \$table = '{$model_table_name}';\n";
            $class_text .= "    protected \$pk = '{$pk_field_name}';\n";
            $class_text .= "    \n";
            $class_text .= "}\n";

            $file_name = $export_path.DIRECTORY_SEPARATOR."{$class_name}.php";
            file_put_contents($file_name, $class_text);
        }
    }
}
