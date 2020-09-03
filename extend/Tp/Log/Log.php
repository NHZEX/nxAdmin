<?php
declare(strict_types=1);

namespace Tp\Log;

use think\Manager;
use function array_merge;

class Log extends \think\Log
{
    public function createDriver(string $name)
    {
        $driver = Manager::createDriver($name);

        $lazy  = !$this->getChannelConfig($name, "realtime_write", false) && !$this->app->runningInConsole();
        $allow = array_merge($this->getConfig("level", []), $this->getChannelConfig($name, "level", []));

        return new Channel($name, $driver, $allow, $lazy, $this->app->event);
    }
}
