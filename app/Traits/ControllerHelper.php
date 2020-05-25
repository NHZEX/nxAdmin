<?php
declare(strict_types=1);

namespace app\Traits;

use Closure;
use think\db\Query;
use think\Request;
use function app;
use function array_filter;
use function array_key_first;
use function array_merge;
use function count;
use function explode;
use function is_array;
use function is_callable;
use function is_int;
use function str_contains;

/**
 * Trait ControllerHelper
 * @package app\Traits
 * @property Request $request
 */
trait ControllerHelper
{
    /**
     * 数据表字段名 - 页面属性，映射
     * @param array $mapping
     * @return array
     */
    public function buildParam(array $mapping)
    {
        $data  = [];
        $input = $this->request->param();
        foreach ($mapping as $name => $alias) {
            if (is_int($name)) {
                $name = $alias;
            }
            if (isset($input[$alias])) {
                $data[$name] = $input[$alias];
            }
        }
        return $data;
    }

    /**
     * 组合筛选条件
     * @param array $input 输入数据
     * @param array $where 筛选设置 <string, string, string|null, callable|null, string[]|null>
     *                     ['字段名', '操作符', '值', 'empty' => '验证方式', 'find' => '使用字段别名']
     * @return array
     */
    public function buildWhere($input, $where)
    {
        $data = [];
        foreach ($where as $key => $item) {
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
                    if (isset($item['empty'])) {
                        if (is_callable($item['empty']) && !$item['empty']($condition)) {
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
     * 组合筛选条件 (支持闭包)
     * @param array $input
     * @param array $where
     * @return Closure
     * @see buildWhere
     */
    public function buildWhereClosure(array $input, array $where): Closure
    {
        return function (Query $query) use ($input, $where) {
            $tableName = $query->getTable();
            $tableName = is_array($tableName) ? $tableName[array_key_first($tableName)] : $tableName;

            $where  = $this->buildWhere($input, $where);
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
     * @return array [<string>$key => <string>$order]
     */
    public function buildOrder(?array $input = null, string $orderField = '_sort'): ?array
    {
        if ($input === null) {
            $sort = app()->request->param($orderField);
        } else {
            $sort = $input[$orderField] ?? null;
        }
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
        return [
            $sort[0] => $sort[1],
        ];
    }
}
