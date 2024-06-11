<?php

declare(strict_types=1);

namespace app\Service\System;

use PDOException;
use RuntimeException;
use think\db\exception\DbException;
use think\db\PDOConnection;
use function Zxin\Arr\array_group;
use function Zxin\Util\format_byte;

class DatabaseUtils
{
    public static function queryTabelInfo(bool $newStructure = false): array
    {
        $db = app()->db;
        $connections = $db->getConfig('connections');

        $output = [];

        foreach ($connections as $name => $config) {
            $version = null;
            try {
                $connection = $db->connect($name);
                $list = $connection
                    ->table('information_schema.tables')
                    ->field([
                        'table_schema', 'table_name', 'auto_increment', 'table_rows',
                        'avg_row_length', 'data_length', 'index_length', 'data_free',
                        'create_time', 'update_time', 'table_comment', 'table_collation',
                    ])
                    ->whereRaw('table_schema=SCHEMA()')
                    ->order('table_name', 'asc')
                    ->select();

                $partitions = $connection
                    ->table('information_schema.partitions')
                    ->whereRaw('table_schema=SCHEMA()')
                    ->whereNotNull('PARTITION_METHOD')
                    ->select();

                $partitions = array_group($partitions->toArray(), 'TABLE_NAME', false);

                $list = $list->map(function ($item) use ($partitions) {
                    $output = [];
                    foreach ($item as $key => $value) {
                        $output[strtolower($key)] = $value;
                    }

                    $output['human'] = [
                        'avg_row_size' => format_byte($output['avg_row_length'], 3),
                        'total_size' => format_byte($output['data_length'] + $output['index_length'], 3),
                        'data_size' => format_byte($output['data_length'], 3),
                        'index_size' => format_byte($output['index_length'], 3),
                        'data_free_size' => format_byte($output['data_free'], 3),
                    ];

                    $partition = $partitions[$output['table_name']] ?? null;

                    if ($partition) {
                        $partition = array_map(function ($val) {
                            $output = array_change_key_case($val);
                            $output = array_filter($output, fn ($key) => \in_array($key, [
                                'table_name',
                                'partition_name',
                                'subpartition_name',
                                'partition_method',
                                'subpartition_method',
                                'partition_expression',
                                'subpartition_expression',
                                'partition_description',
                                'table_rows',
                                'avg_row_length',
                                'data_length',
                                'max_data_length',
                                'index_length',
                                'data_free',
                                'create_time',
                                'update_time',
                                'check_time',
                            ]), \ARRAY_FILTER_USE_KEY);

                            $output['id'] = "{$output['table_name']}_{$output['partition_name']}_{$output['subpartition_name']}";

                            $output['human'] = [
                                'avg_row_size' => format_byte($output['avg_row_length'], 3),
                                'data_size' => format_byte($output['data_length'], 3),
                                'index_size' => format_byte($output['index_length'], 3),
                                'total_size' => format_byte($output['data_length'] + $output['index_length'], 3),
                                'data_free_size' => format_byte($output['data_free'], 3),
                            ];

                            return $output;
                        }, $partition);
                    }

                    $output['partition'] = $partition;

                    return $output;
                });
            } catch (DbException $e) {
                $list = [];
                $message = $e->getMessage();
                log_warning((string) $e);
            }

            try {
                if (isset($connection) && $connection instanceof PDOConnection) {
                    $version = db_version($connection, true);
                }
            } catch (DbException|PDOException|RuntimeException $e) {
                $message = $e->getMessage();
                log_warning((string) $e);
            }

            if ($newStructure) {
                $output[] = [
                    'name' => $name,
                    'table' => $list,
                    'version' => $version ?? 'unknown',
                    'message' => $message ?? null,
                ];
            } else {
                $output[] = [
                    'name' => $name,
                    'tables' => $list, // 兼容代码
                    'version' => $version ?? 'unknown',
                    'message' => $message ?? null,
                ];
            }
        }

        return $output;
    }
}
