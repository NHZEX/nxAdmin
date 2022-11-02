<?php

namespace app\Helper;

use Closure;
use think\db\Query;
use function array_filter;
use function array_merge;
use function call_user_func;
use function count;
use function explode;
use function is_array;
use function is_callable;

class WhereHelper
{
    /**
     * 构建筛选条件
     * @param array                                                                                        $input 输入数据
     * @param array<array{0:string, 1: string, 2?: string, empty?: callable|(callable(string, array): bool), find?: array<string>|callable (array, string): string}> $where
     *        筛选设置 ['字段名', '操作符', '值', 'empty' => '值验证回调', 'find' => '值来源字段名']
     * @return array
     */
    public static function buildWhere(array $input, array $where): array
    {
        $data = [];
        foreach ($where as $item) {
            if (count($item) >= 2) {
                [$whereField, $op] = $item;
                $inputField  = $whereField;
                $inputFields = [];

                if (isset($item['find'])) {
                    $find = $item['find'];
                    if (is_callable($find)) {
                        $inputFields[] = $find($input, $inputField);
                    } elseif (is_array($find)) {
                        $inputFields = array_merge($inputFields, $find);
                    }
                } else {
                    $inputFields[] = $inputField;
                }

                foreach ($inputFields as $field) {
                    if (!isset($input[$field])) {
                        continue;
                    }
                    $condition = $input[$field];
                    // transform
                    if (isset($item['tf']) && is_callable($item['tf'])) {
                        $condition = call_user_func($item['tf'], $condition);
                    }
                    if (isset($item['empty'])) {
                        if (is_callable($item['empty']) && !$item['empty']($condition, $input)) {
                            continue;
                        }
                    } else {
                        if (empty($condition)) {
                            continue;
                        }
                    }
                    $parse  = $item[2] ?? null;
                    $data[] = [
                        $whereField,
                        $op,
                        (isset($parse) && is_callable($parse)) ? $parse($condition, $field) : $condition,
                    ];
                    break;
                }
            }
        }
        return $data;
    }

    /**
     * 构建筛选条件 (延迟闭包)
     * @param array $input
     * @param array $where
     * @return Closure
     * @see buildWhere
     */
    public static function buildWhereClosure(array $input, array $where): Closure
    {
        return function (Query $query) use ($input, $where) {
            $tableName = $query->getTable();
            $tableName = is_array($tableName) ? $tableName[array_key_first($tableName)] : $tableName;

            $where  = static::buildWhere($input, $where);
            $output = [];
            foreach ($where as $value) {
                $value[0] = "{$tableName}.{$value[0]}";
                $output[] = $value;
            }
            $query->where($output);
        };
    }

    /**
     * @param array  $input
     * @param string $orderField
     * @return array{string, string}|null [$field => $order]
     */
    public static function buildOrder(array $input, string $orderField = '_sort', ?string $tableName = null): ?array
    {
        $sort = $input[$orderField] ?? null;
        if (empty($sort) || !str_contains($sort, ':')) {
            return null;
        }
        $sort = array_filter(explode(':', $sort, 2));
        if (count($sort) !== 2) {
            return null;
        }
        if ($sort[1] !== 'asc' && $sort[1] !== 'desc') {
            return null;
        }
        $fieldName = $tableName ? "{$tableName}.{$sort[0]}" : $sort[0];
        return [
            $fieldName => $sort[1],
        ];
    }
}
