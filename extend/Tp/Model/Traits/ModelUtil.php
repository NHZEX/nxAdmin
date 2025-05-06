<?php

namespace Tp\Model\Traits;

use Closure;
use InvalidArgumentException;
use think\db\Raw;
use think\facade\Db;

trait ModelUtil
{
    /**
     * think orm 创建一个查询表达式.
     */
    public static function dbRaw(string $value): Raw
    {
        return Db::raw($value);
    }

    /**
     * 当前模型是否存在该字段.
     */
    public function hasData(string $field): bool
    {
        try {
            return isset($this[$field]);
        } catch (InvalidArgumentException $exception) {
            return false;
        }
    }

    public function hasRawData(string $field): bool
    {
        return array_key_exists($field, $this->getData());
    }

    /**
     * 自定义查询集合.
     *
     * @param array  $data
     * @param array  $query
     * @param string $mapName
     */
    protected static function setQueryMap($data = [], $query = [], $mapName = 'queryMap'): array
    {
        $map = [];

        if (!isset($query[$mapName])) {
            return $map;
        }

        $queryMap = $query[$mapName];
        foreach ($data as $key => $value) {
            if (!isset($queryMap[$key]) || ('' === $queryMap[$key])) {
                continue;
            }

            if (3 === \count($value)) {
                // 若$callBack是钩子函数则需要返回相应的值, 否则直接添加到数组中
                [$field, $expression, $callBack] = $value;
                if ($callBack instanceof Closure) {
                    $value = [$field, $expression, $callBack($queryMap[$key])];
                }
                $map[] = $value;
            } elseif (2 === \count($value)) {
                // 自定义表达式
                [$field, $expression] = $value;
                $map[] = [$field, $expression, $queryMap[$key]];
            }
        }

        return $map;
    }
}
