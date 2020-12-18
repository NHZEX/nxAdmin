<?php

namespace app\Validate;

use Zxin\Think\Validate\ValidateBase;
use function filter_var;
use const FILTER_VALIDATE_INT;

abstract class Base extends ValidateBase
{
    /**
     * 判断是否为正整数
     * @param $value
     * @return bool
     */
    protected function isPositiveInteger($value)
    {
        return $this->isInteger($value, '+');
    }

    /**
     * 判断是否为整数
     * @param $value
     * @param $params
     * @return bool|string
     */
    protected function isInteger($value, $params)
    {
        if (($result = filter_var($value, FILTER_VALIDATE_INT)) === false) {
            return ':attribute不是一个整数';
        }
        if (empty($params)) {
            return true;
        }
        $positive = str_contains($params, '+');
        $negative  = str_contains($params, '-');
        if ($positive && $result >= 0) {
            return true;
        } elseif (!$negative) {
            return ':attribute不是一个正数';
        } elseif ($result < 0) {
            return true;
        } else {
            return ':attribute不是一个负数';
        }
    }
}
