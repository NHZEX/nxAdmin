<?php
/**
 * Created by PhpStorm.
 * User: NHZEXG
 * Date: 2019/2/22
 * Time: 13:40
 */

namespace app\Facade;

use think\Facade;
use think\facade\App;

class Base extends Facade
{
    /**
     * 实例是否存在
     * @return bool
     */
    public static function hasInstance(): bool
    {
        /** @noinspection PhpDynamicAsStaticMethodCallInspection */
        return App::exists(static::getFacadeClass());
    }

    /**
     * 获取当前实例
     * @return object
     */
    public static function getSelf()
    {
        return self::createFacade();
    }
}
