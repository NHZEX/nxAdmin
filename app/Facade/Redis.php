<?php
/**
 * Created by PhpStorm.
 * User: NHZEXG
 * Date: 2019/3/11
 * Time: 13:20
 */

namespace app\Facade;

use app\Service\Redis\RedisProvider;

/**
 * Class Redis
 * @package app\Facade
 * @mixin RedisProvider
 * @method RedisProvider instance(...$args) static
 * @method void setConfig(array $cfg, $reconnect = false) static
 * @method RedisProvider addStoresHosting(RedisProvider $storage) static
 */
class Redis extends Base
{
    public static function getFacadeClass()
    {
        return 'redis';
    }

    /**
     * @param array $args
     * @param bool  $newInstance
     * @return object
     */
    public static function newMake(array $args = [], bool $newInstance = false)
    {
        return self::createFacade('', $args, $newInstance);
    }
}
