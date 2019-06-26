<?php
declare(strict_types=1);

namespace app\common\Handle;

use app\Server\RedisProxy;
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
        /** @var RedisProxy $redis */
        $redis = $container->make(RedisProxy::class);
        $redis->closeLink();
    }
}
