<?php

declare(strict_types=1);

namespace Tp\Model\Traits;

use Exception;
use stdClass;
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
use function strpos;
use function strtotime;
use function unserialize;

/**
 * Trait Attribute
 * @package Tp\Model\Traits
 * 如果tp-orm同意合入则可以移除
 *
 * @property string $dateFormat
 * @method formatDateTime($format, $time = 'now', bool $timestamp = false)
 */
trait Attribute
{
    /**
     * @inheritDoc
     */
    protected function readTransform($value, $type)
    {
        $param = null;
        if ($value === null) {
            return null;
        }

        if (is_array($type)) {
            [$type, $param] = $type;
        } elseif (strpos($type, ':')) {
            [$type, $param] = explode(':', $type, 2);
        }

        switch ($type) {
            case 'integer':
                $value = (int) $value;
                break;
            case 'float':
                if (empty($param)) {
                    $value = (float) $value;
                } else {
                    $value = (float) number_format($value, (int) $param, '.', '');
                }
                break;
            case 'boolean':
                $value = (bool) $value;
                break;
            case 'timestamp':
                $format = !empty($param) ? $param : $this->dateFormat;
                $value  = $this->formatDateTime($format, $value, true);
                break;
            case 'datetime':
                $format = !empty($param) ? $param : $this->dateFormat;
                $value  = $this->formatDateTime($format, $value);
                break;
            case 'json':
                $value = json_decode($value, true);
                break;
            case 'array':
                $value = empty($value) ? [] : json_decode($value, true);
                break;
            case 'object':
                $value = empty($value) ? new stdClass() : json_decode($value);
                break;
            case 'serialize':
                try {
                    $value = unserialize($value);
                } catch (Exception $e) {
                    $value = null;
                }
                break;
            default:
                if (class_exists($type)) {
                    if (is_subclass_of($type, FieldTypeTransform::class)) {
                        $value = $type::modelReadValue($value, $this);
                    } else {
                        // 对象类型
                        $value = new $type($value);
                    }
                }
        }

        return $value;
    }

    /**
     * @inheritDoc
     */
    protected function writeTransform($value, $type)
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
        } elseif (strpos($type, ':')) {
            [$type, $param] = explode(':', $type, 2);
        }

        switch ($type) {
            case 'integer':
                $value = (int) $value;
                break;
            case 'float':
                if (empty($param)) {
                    $value = (float) $value;
                } else {
                    $value = (float) number_format($value, (int) $param, '.', '');
                }
                break;
            case 'boolean':
                $value = (bool) $value;
                break;
            case 'timestamp':
                if (!is_numeric($value)) {
                    $value = strtotime($value);
                }
                break;
            case 'datetime':
                $value = is_numeric($value) ? $value : strtotime($value);
                $value = $this->formatDateTime('Y-m-d H:i:s.u', $value, true);
                break;
            case 'object':
                if (is_object($value)) {
                    $value = json_encode($value, JSON_FORCE_OBJECT);
                }
                break;
            case 'array':
                $value = (array) $value;
                // no break
            case 'json':
                $option = !empty($param) ? (int) $param : JSON_UNESCAPED_UNICODE;
                $value  = json_encode($value, $option);
                break;
            case 'serialize':
                $value = serialize($value);
                break;
            default:
                if (class_exists($type)) {
                    if (is_subclass_of($type, FieldTypeTransform::class)) {
                        $value = $type::modelWriteValue($value, $this);
                    } elseif (is_object($value) && method_exists($value, '__toString')) {
                        // 对象类型
                        $value = $value->__toString();
                    }
                }
        }

        return $value;
    }
}
