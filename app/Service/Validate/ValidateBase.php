<?php

namespace app\Service\Validate;

abstract class ValidateBase extends \Zxin\Think\Validate\ValidateBase
{
    /**
     * 判断是否为整数
     * @param $value
     * @return bool
     */
    protected function isPositiveInteger($value)
    {
        if (filter_var($value, FILTER_VALIDATE_INT) && $value > 0) {
            return true;
        } else {
            return false;
        }
    }
}
