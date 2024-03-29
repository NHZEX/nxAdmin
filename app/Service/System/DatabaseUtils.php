<?php
declare(strict_types=1);

namespace app\Service\System;
use think\db\exception\DbException;
use think\db\PDOConnection;
use function Zxin\Util\format_byte;

class DatabaseUtils
{
    public static function queryTabelInfo(): array
    {
        $db = \app()->db;
        $connections = $db->getConfig('connections');

        $output = [];

        foreach ($connections as $name => $config) {
            $version = null;
            try {
                $connection = $db->connect($name);
                $list = $connection
                    ->table('information_schema.tables')
                    ->field([
                        'table_schema', 'table_name', 'auto_increment','table_rows',
                        'avg_row_length', 'data_length', 'index_length', 'data_free',
                        'create_time', 'update_time', 'table_comment',
                    ])
                    ->whereRaw('table_schema=SCHEMA()')
                    ->order('table_name', 'asc')
                    ->select();

                $list = $list->map(function ($item) {
                    $output = [];
                    foreach ($item as $key => $value) {
                        $output[\strtolower($key)] = $value;
                    }

                    $output['human'] = [
                        'avg_row_size' => format_byte($output['avg_row_length'], 3),
                        'total_size' => format_byte($output['data_length'] + $output['index_length'], 3),
                        'data_size' => format_byte($output['data_length'], 3),
                        'index_size' => format_byte($output['index_length'], 3),
                        'data_free_size' => format_byte($output['data_free'], 3),
                    ];

                    return $output;
                });
            } catch (DbException $e) {
                $list = [];
                $message = $e->getMessage();
                \log_warning((string) $e);
            }

            try {
                if (isset($connection) && $connection instanceof PDOConnection) {
                    $version = \db_version($connection, true);
                }
            } catch (DbException|\PDOException|\RuntimeException $e) {
                $message = $e->getMessage();
                \log_warning((string) $e);
            }

            $output[] = [
                'name' => $name,
                'tables' => $list,
                'version' => $version ?? 'unknown',
                'message' => $message ?? null,
            ];
        }

        return $output;
    }
}
