<?php

namespace app\Validate;

use think\Validate;
use Zxin\Think\Validate\ValidateBase;
use function array_map;
use function filter_var;
use function is_array;
use function join;
use const FILTER_VALIDATE_INT;

abstract class Base extends ValidateBase
{
    /**
     * 判断是否为正整数
     * @param string|int $value
     * @return bool
     */
    protected function isPositiveInteger($value)
    {
        return $this->isNumber($value, 'int+');
    }

    /**
     * 判断是否为整数
     * @param string|int $value
     * @param string|null $params
     * @return bool|string
     */
    protected function isInteger($value, ?string $params)
    {
        return $this->isNumber($value, "int{$params}");
    }

    /**
     * 判断是否为数值
     * @param string|int $value
     * @param string|null $params
     * @return bool|string
     */
    protected function isNumber($value, ?string $params)
    {
        $isInt = str_contains($params, 'int');
        if (($result = filter_var($value, $isInt ? FILTER_VALIDATE_INT : FILTER_VALIDATE_FLOAT)) === false) {
            if ($isInt) {
                return ':attribute必须是一个整数';
            } else {
                return ':attribute必须是一个数值';
            }
        }
        if (empty($params)) {
            return true;
        }
        $positive = str_contains($params, '+');
        $negative  = str_contains($params, '-');
        if ($positive && $negative) {
            return true;
        } elseif ($positive && $result >= 0) {
            return true;
        } elseif (!$negative) {
            return ':attribute必须是一个正数';
        } elseif ($result < 0) {
            return true;
        } else {
            return ':attribute必须是一个负数';
        }
    }

    /**
     * @param Validate $valid
     * @param array    $value
     * @return true|string
     */
    public static function subValidateCall(Validate $valid, array $value)
    {
        if ($valid->check($value) === false) {
            $error = $valid->getError();
            if (is_array($error)) {
                return join(', ', array_map(function ($str) {
                    return ":attribute->{$str}";
                }, $error));
            } else {
                return ":attribute->{$error}";
            }
        }
        return true;
    }
}
