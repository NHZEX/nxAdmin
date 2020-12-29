<?php
declare(strict_types=1);

namespace app\Traits\Model;

use Exception;
use TypeError;
use function class_exists;
use function get_class;
use function is_array;
use function is_callable;
use function is_string;
use function is_subclass_of;
use function json_decode;
use function json_encode;
use function substr;

trait ModelAttrObjectCache
{
    protected $cacheStorage = [];

    /**
     * @param string          $funcName
     * @param mixed           $value
     * @param string|callable $className
     * @return mixed|null
     * @throws Exception
     */
    protected function attrObjectCacheGet(string $funcName, $value, $className)
    {
        $key = substr($funcName, 3);

        if (isset($this->cacheStorage[$key])) {
            return $this->cacheStorage[$key];
        }
        if (empty($value)) {
            return null;
        }
        $value = json_decode($value, true);
        if (!is_array($value)) {
            return null;
        }
        if (is_string($className) && class_exists($className)) {
            return $this->cacheStorage[$key] = (new $className($value));
        } elseif (is_callable($className)) {
            return $this->cacheStorage[$key] = $className($value);
        } else {
            throw new Exception('无法处理的值转换');
        }
    }

    /**
     * @param string $funcName
     * @param object $value
     * @param string $className
     * @return false|string
     * @throws Exception
     */
    protected function attrObjectCacheSet(string $funcName, object $value, string $className)
    {
        $key = substr($funcName, 3);

        if (is_string($className) && class_exists($className)) {
            $vail = get_class($value) === $className || is_subclass_of($value, $className);
        } elseif (is_callable($className)) {
            $vail = $className($value);
        } else {
            throw new Exception('无法处理的值转换');
        }
        if (!$vail) {
            throw new TypeError('数据类型错误');
        }
        $this->cacheStorage[$key] = $value;
        return json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}
