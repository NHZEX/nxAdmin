<?php
declare(strict_types=1);

namespace app\Service\Swoole;

use app\Service\Redis\RedisProvider;
use HZEX\TpSwoole\Container\Destroy\DestroyContract;
use think\Container;

class DestroyRedisConnection implements DestroyContract
{

    /**
     * "handle" function for clean.
     *
     * @param Container $container
     */
    public function handle(Container $container): void
    {
        /** @var RedisProvider $redis */
        $redis = $container->make(RedisProvider::class);
        $redis->closeLink();
    }
}
