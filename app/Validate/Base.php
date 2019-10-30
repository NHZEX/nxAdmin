<?php
/**
 * Created by PhpStorm.
 * User: NHZEXG
 * Date: 2018/11/22
 * Time: 10:37
 */

namespace app\Validate;

use think\Validate;

abstract class Base extends Validate
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
