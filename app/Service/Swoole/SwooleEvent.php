<?php
declare(strict_types=1);

namespace app\Service\Swoole;

use app\Traits\QuickHelper;
use RuntimeException;
use Swoole\Server;
use Swoole\Timer;
use think\App;
use think\Event;
use think\swoole\GlobalEvent;
use Zxin\Think\Redis\RedisManager;
use function chgrp;
use function chmod;
use function chown;
use function env;
use function extension_loaded;
use function file_exists;
use function phpversion;
use function unlink;
use function version_compare;

class SwooleEvent extends GlobalEvent
{
    use QuickHelper;

    protected $app;

    /** @var Server|\Swoole\Http\Server|\Swoole\WebSocket\Server */
    protected $serv;

    /** @var string|null */
    protected $unixsock;

    public function __construct(App $app)
    {
        $this->app = $app;

        $this->unixsock = env('SERV_HTTP_UNIXSOCK');
    }

    public function subscribe(Event $event)
    {
        $event->listen('swoole.init', self::callWrap([$this, 'init']));
        $event->listen('swoole.start', self::callWrap([$this, 'start']));
        $event->listen('swoole.shutdown', self::callWrap([$this, 'shutdown']));
        $event->listen('swoole.managerStart', self::callWrap([$this, 'managerStart']));
        $event->listen('swoole.managerStop', self::callWrap([$this, 'managerStop']));
        $event->listen('swoole.workerStart', self::callWrap([$this, 'workerStart']));
        $event->listen('swoole.workerStop', self::callWrap([$this, 'workerStop']));
        $event->listen('swoole.workerExit', self::callWrap([$this, 'workerExit']));
        $event->listen('swoole.pipeMessage', self::callWrap([$this, 'pipeMessage']));
    }

    protected function init()
    {
        echo "> serv init\n";
        if (!extension_loaded('swoole')) {
            throw new RuntimeException('swoole extension does not exist');
        }
        if (version_compare(phpversion('swoole'), '4.4.18', '<')) {
            throw new RuntimeException('swoole extension required >= 4.4.18');
        }
        $this->serv = SwooleService::getServer();
        if ($this->unixsock) {
            if (file_exists($this->unixsock) && !@unlink($this->unixsock)) {
                throw new RuntimeException('unix sock existed: ' . $this->unixsock);
            }
            $this->serv->addlistener($this->unixsock, 0, SWOOLE_UNIX_STREAM);
        }
    }

    protected function start()
    {
        echo "> serv start#{$this->serv->master_pid}\n";
    }

    protected function shutdown()
    {
        echo "> serv shutdown#{$this->serv->master_pid}\n";
    }

    protected function managerStart()
    {
        echo "> manager start#{$this->serv->manager_pid}\n";
        if ($this->unixsock) {
            @chmod($this->unixsock, 0777);
            if (isset($this->serv->setting['user'])) {
                @chown($this->unixsock, $this->serv->setting['user']);
            }
            if (isset($this->serv->setting['group'])) {
                @chgrp($this->unixsock, $this->serv->setting['group']);
            }
        }
    }

    protected function managerStop()
    {
        echo "> manager stop#{$this->serv->manager_pid}\n";
    }

    protected function workerStart()
    {
        echo "> worker start#{$this->serv->worker_id}:{$this->serv->worker_pid}\n";
    }

    protected function workerStop()
    {
        RedisManager::destroy();
        echo "> worker stop#{$this->serv->worker_id}:{$this->serv->worker_pid}\n";
    }

    protected function workerExit()
    {
        RedisManager::destroy();
        echo "> worker exit#{$this->serv->worker_id}:{$this->serv->worker_pid}\n";
        Timer::clearAll();
    }

    protected function pipeMessage($params)
    {
        // [, $workerId, $message] = $params;
    }
}
