<?php
declare(strict_types=1);

namespace app\Traits;

use think\Request;
use function array_merge;
use function count;
use function is_array;
use function is_callable;
use function is_int;

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
        $data = [];
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
                $inputField = $whereField;
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
                    $parse = $item[2] ?? null;
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
}
