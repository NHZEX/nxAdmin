<?php
/**
 * Created by PhpStorm.
 * User: NHZEXG
 * Date: 2018/11/22
 * Time: 10:37
 */

namespace app\validate;


use think\Request;
use think\Validate;

abstract class Base extends Validate
{
    /**
     * 判断是否为整数
     * User: Johnson
     * @param $value
     * @return bool
     */
    protected function isPositiveInteger($value)
    {
        if(filter_var($value, FILTER_VALIDATE_INT) && $value > 0){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 询问当前应当使用何种场景
     * @param Request $request
     * @return string|null
     */
    public static function askScene(Request $request)
    {
        return null;
    }
}
