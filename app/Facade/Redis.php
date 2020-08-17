<?php

namespace app\Facade;

use Zxin\Redis\Connections\PhpRedisConnection;
use Zxin\Think\Redis\RedisManager;

/**
 * Class Redis
 * @package app\Facade
 * @mixin RedisManager
 * @method RedisManager instance(...$args) static
 * @method PhpRedisConnection connection($name = null) static
 */
class Redis extends Base
{
    public static function getFacadeClass()
    {
        return 'redis';
    }
}
