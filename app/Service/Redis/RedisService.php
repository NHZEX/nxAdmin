<?php
declare(strict_types=1);

namespace app\Service\Redis;

use think\Service;

class RedisService extends Service
{
    public function register()
    {
        $this->app->bind('redis', RedisProvider::class);
    }

    public function boot()
    {
    }
}
