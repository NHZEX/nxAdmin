<?php
declare(strict_types=1);

namespace app\Service\Swoole;

use app\Service\Redis\RedisProvider;
use HZEX\TpSwoole\Contract\ContractDestroyInterface;
use think\Container;

class DestroyRedisConnection implements ContractDestroyInterface
{

    /**
     * "handle" function for clean.
     *
     * @param Container $container
     */
    public function handle(Container $container): void
    {
        /** @var RedisProvider $redis */
        if ($container->exists(RedisProvider::class)) {
            $redis = $container->make(RedisProvider::class);
            $redis->closeLink();
        }
    }
}
