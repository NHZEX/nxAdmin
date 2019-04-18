<?php
/**
 * Created by PhpStorm.
 * User: NHZEXG
 * Date: 2019/3/11
 * Time: 13:20
 */

namespace app\Facade;

use app\Server\RedisProxy;
use Redis\RedisExtend;

/**
 * Class Redis
 * @package facade
 * @mixin RedisProxy
 * @method RedisExtend getSelf() static
 * @method void setConfig(array $cfg, $reconnect = false) static
 */
class Redis extends Base
{
    public static function getFacadeClass()
    {
        return 'redis';
    }
}
