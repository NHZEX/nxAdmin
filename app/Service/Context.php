<?php

namespace app\Service;

use ArrayObject;
use Closure;
use think\App;

class Context
{
    protected $object;

    /**
     * 获取上下文.
     */
    public static function get(): self
    {
        return App::getInstance()->make(self::class);
    }

    public static function getDataObject(): ArrayObject
    {
        $context = self::get();
        if (empty($context->object)) {
            $context->object = new ArrayObject();
        }

        return $context->object;
    }

    /**
     * 获取临时数据.
     *
     * @param null $default
     *
     * @return mixed|null
     */
    public static function getData(string $key, $default = null)
    {
        if (self::hasData($key)) {
            return self::getDataObject()->offsetGet($key);
        }

        return $default;
    }

    /**
     * 判断是否存在临时数据.
     */
    public static function hasData(string $key): bool
    {
        return self::getDataObject()->offsetExists($key);
    }

    /**
     * 写入临时数据.
     */
    public static function setData(string $key, $value): void
    {
        self::getDataObject()->offsetSet($key, $value);
    }

    /**
     * 删除数据.
     */
    public static function removeData(string $key): void
    {
        if (self::hasData($key)) {
            self::getDataObject()->offsetUnset($key);
        }
    }

    /**
     * 如果不存在则写入数据.
     *
     * @return mixed|null
     */
    public static function rememberData(string $key, $value)
    {
        if (self::hasData($key)) {
            return self::getData($key);
        }

        if ($value instanceof Closure) {
            // 获取缓存数据
            $value = $value();
        }

        self::setData($key, $value);

        return $value;
    }

    /**
     * @internal
     * 清空数据
     */
    public static function clear(): void
    {
        self::getDataObject()->exchangeArray([]);
    }
}
