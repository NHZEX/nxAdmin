<?php
declare(strict_types=1);

namespace app\Service\Swoole;

use Swoole\Server;
use think\Service;
use think\swoole\GlobalEvent;
use think\swoole\Manager;

class SwooleService extends Service
{
    public function register()
    {
    }

    /**
     * @return Server
     */
    public static function getServer()
    {
        return Manager::getInstance()->getServer();
    }

    /**
     * @return SwooleEvent|GlobalEvent
     */
    public static function getEvent(): SwooleEvent
    {
        return Manager::getInstance()->getGlobalEvent();
    }
}
