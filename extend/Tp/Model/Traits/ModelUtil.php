<?php
/**
 * Created by PhpStorm.
 * User: NHZEXG
 * Date: 2018/5/22
 * Time: 17:07
 */

namespace Tp\Model\Traits;

use Closure;
use InvalidArgumentException;
use think\db\Raw;
use think\facade\Db;

trait ModelUtil
{
    /**
     * think orm 创建一个查询表达式
     * @param $value
     * @return Raw
     * @author NHZEXG
     */
    public static function dbRaw($value)
    {
        return Db::raw($value);
    }

    /**
     * 当前模型是否存在该字段
     * @param $field
     * @author NHZEXG
     * @return bool
     */
    public function hasData($field)
    {
        try {
            return isset($this[$field]);
        } catch (InvalidArgumentException $exception) {
            return false;
        }
    }

    /**
     * 自定义查询集合
     * @param array  $data
     * @param array  $query
     * @param string $mapName
     * @return array
     */
    protected static function setQueryMap($data = [], $query = [], $mapName = 'queryMap')
    {
        $map = [];

        if (!isset($query[$mapName])) {
            return $map;
        }

        $queryMap = $query[$mapName];
        foreach ($data as $key => $value) {
            if (!isset($queryMap[$key]) || ($queryMap[$key] === '')) {
                continue;
            }

            if (count($value) === 3) {
                //若$callBack是钩子函数则需要返回相应的值, 否则直接添加到数组中
                list($field, $expression, $callBack) = $value;
                if ($callBack instanceof Closure) {
                    $value = [$field, $expression, $callBack($queryMap[$key])];
                }
                array_push($map, $value);
            } elseif (count($value) === 2) {
                //自定义表达式
                list($field, $expression) = $value;
                array_push($map, [$field, $expression, $queryMap[$key]]);
            }
        }
        return $map;
    }
}
