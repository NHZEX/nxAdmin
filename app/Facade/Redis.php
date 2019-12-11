<?php
/**
 * Created by PhpStorm.
 * User: NHZEXG
 * Date: 2019/3/11
 * Time: 13:20
 */

namespace app\Facade;

use app\Service\Redis\Connections\PhpRedisConnection;
use app\Service\Redis\RedisProvider;

/**
 * Class Redis
 * @package app\Facade
 * @mixin RedisProvider
 * @method RedisProvider instance(...$args) static
 * @method PhpRedisConnection connection($name = null) static
 */
class Redis extends Base
{
    public static function getFacadeClass()
    {
        return 'redis';
    }
}
