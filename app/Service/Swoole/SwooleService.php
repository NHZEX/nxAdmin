<?php
declare(strict_types=1);

namespace app\Service\Swoole;

use Swoole\Server;
use think\Service;
use function app;

class SwooleService extends Service
{
    public function register()
    {
        $this->app->event->subscribe(SwooleEvent::class);
    }

    /**
     * @return Server
     */
    public static function getServer()
    {
        return app()->make(Server::class);
    }
}
