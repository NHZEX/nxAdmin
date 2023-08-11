<?php

declare(strict_types=1);

namespace Tp\Model\Traits;

use think\db\Raw;
use Tp\Model\Contracts\FieldTypeTransform;
use function class_exists;
use function explode;
use function is_array;
use function is_numeric;
use function is_object;
use function is_subclass_of;
use function json_decode;
use function json_encode;
use function method_exists;
use function number_format;
use function serialize;
use function strtotime;
use function unserialize;

/**
 * Trait Attribute
 * @package Tp\Model\Traits
 * 如果pr能合入则可以移除
 */
trait Attribute
{
    /**
     * @inheritDoc
     */
    protected function readTransform($value, string|array $type)
    {
        $param = null;
        if ($value === null) {
            return null;
        }

        if (is_array($type)) {
            [$type, $param] = $type;
        } elseif (str_contains($type, ':')) {
            [$type, $param] = explode(':', $type, 2);
        }

        $call = function ($value) {
            try {
                $value = unserialize($value);
            } catch (\Exception $e) {
                $value = null;
            }
            return $value;
        };

        $exTransform = static function (string $type, $value, $model) {
            if (class_exists($type)) {
                if (is_subclass_of($type, FieldTypeTransform::class)) {
                    $value = $type::modelReadValue($value, $model);
                } else {
                    // 对象类型
                    $value = new $type($value);
                }
            }

            return $value;
        };

        return match ($type) {
            'integer'   =>  (int) $value,
            'float'     =>  empty($param) ? (float) $value : (float) number_format($value, (int) $param, '.', ''),
            'boolean'   =>  (bool) $value,
            'timestamp' =>  !is_null($value) ? $this->formatDateTime(!empty($param) ? $param : $this->dateFormat, $value, true) : null,
            'datetime'  =>  !is_null($value) ? $this->formatDateTime(!empty($param) ? $param : $this->dateFormat, $value) : null,
            'json'      =>  json_decode($value, true),
            'array'     =>  empty($value) ? [] : json_decode($value, true),
            'object'    =>  empty($value) ? new \stdClass() : json_decode($value),
            'serialize' =>  $call($value),
            default     =>  $exTransform($type, $value, $this),
        };
    }

    /**
     * @inheritDoc
     */
    protected function writeTransform($value, string|array $type)
    {
        $param = null;
        if ($value === null) {
            return null;
        }

        if ($value instanceof Raw) {
            return $value;
        }

        if (is_array($type)) {
            [$type, $param] = $type;
        } elseif (str_contains($type, ':')) {
            [$type, $param] = explode(':', $type, 2);
        }

        $exTransform = static function (string $type, $value, $model) {
            if (class_exists($type)) {
                if (is_subclass_of($type, FieldTypeTransform::class)) {
                    $value = $type::modelWriteValue($value, $model);
                } elseif (is_object($value) && method_exists($value, '__toString')) {
                    // 后续改进 $value instanceof Stringable
                    // 对象类型
                    $value = $value->__toString();
                }
            }

            return $value;
        };

        return match ($type) {
            'integer'   =>  (int) $value,
            'float'     =>  empty($param) ? (float) $value : (float) number_format($value, (int) $param, '.', ''),
            'boolean'   =>  (bool) $value,
            'timestamp' =>  !is_numeric($value) ? strtotime($value) : $value,
            'datetime'  =>  $this->formatDateTime('Y-m-d H:i:s.u', $value, true),
            'object'    =>  is_object($value) ? json_encode($value, JSON_FORCE_OBJECT) : $value,
            'array'     =>  json_encode((array) $value, !empty($param) ? (int) $param : JSON_UNESCAPED_UNICODE),
            'json'      =>  json_encode($value, !empty($param) ? (int) $param : JSON_UNESCAPED_UNICODE),
            'serialize' =>  serialize($value),
            default     =>  $exTransform($type, $value, $this),
        };
    }
}
