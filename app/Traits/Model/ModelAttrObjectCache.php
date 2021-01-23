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
    private $cacheStorage = [];

    /**
     * @param string          $funcname
     * @param mixed           $value
     * @param string|callable $classname
     * @param bool            $transformJson
     * @return mixed|null
     * @throws Exception
     */
    protected function attrObjectCacheGet(string $funcname, $value, $classname, bool $transformJson = false)
    {
        $key = substr($funcname, 3);

        if (isset($this->cacheStorage[$key])) {
            return $this->cacheStorage[$key];
        }
        if (empty($value)) {
            return null;
        }

        if ($transformJson) {
            if (is_string($value)) {
                $value = json_decode($value, true);
                if (!is_array($value)) {
                    return null;
                }
            } else {
                throw new Exception('无法处理的值转换');
            }
        }

        if (is_string($classname) && class_exists($classname)) {
            return $this->cacheStorage[$key] = new $classname($value);
        } elseif (is_callable($classname)) {
            return $this->cacheStorage[$key] = $classname($value);
        } else {
            throw new Exception('无法处理的值转换');
        }
    }

    /**
     * @param string          $funcname
     * @param object          $value
     * @param string|callable $classname 验证类名或转换方法
     * @param bool            $transformJson
     * @return false|string
     * @throws Exception
     */
    protected function attrObjectCacheSet(string $funcname, object $value, $classname, bool $transformJson = false)
    {
        $key = substr($funcname, 3);

        if (is_string($classname) && class_exists($classname)) {
            if (!(get_class($value) === $classname || is_subclass_of($value, $classname))) {
                throw new TypeError('数据类型错误');
            }
        } elseif (is_callable($classname)) {
            $value = $classname($value);
        } else {
            throw new Exception('无法处理的值转换');
        }

        $this->cacheStorage[$key] = $value;

        if ($transformJson) {
            return json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        } else {
            return $value;
        }
    }
}
