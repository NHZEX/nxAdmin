<?php
declare(strict_types=1);

namespace app\Server;

use Closure;
use HZEX\TpSwoole\Event;
use HZEX\TpSwoole\EventSubscribeInterface;
use HZEX\TpSwoole\Swoole\SwooleServerInterface;
use Smf\ConnectionPool\ConnectionPoolTrait;
use Smf\ConnectionPool\Connectors\PhpRedisConnector;
use Swoole\Http\Server as HttpServer;
use Swoole\Server;
use Swoole\WebSocket\Server as WsServer;
use think\Config;

class ConnectionPool implements SwooleServerInterface, EventSubscribeInterface
{
    use ConnectionPoolTrait;

    private $config = [
        'host' => '127.0.0.1',
        'port' => '6379',
        'password' => '',
        'select' => 0,
        'timeout' => 1,
    ];

    public function __construct(Config $config)
    {
        $this->config = $config->get('redis', []) + $this->config;
    }

    public function subscribe(Event $event): void
    {
        $event->listen('swoole.onWorkerStart', Closure::fromCallable([$this, 'onWorkerStart']));
        $event->listen('swoole.onWorkerStop', Closure::fromCallable([$this, 'onWorkerStop']));
        $event->listen('swoole.onWorkerError', Closure::fromCallable([$this, 'onWorkerError']));
    }

    /**
     * 主进程启动
     * @param Server|HttpServer|WsServer $server
     */
    public function onStart($server): void
    {
    }

    /**
     * 主进程结束
     * @param Server|HttpServer|WsServer $server
     */
    public function onShutdown($server): void
    {
    }

    /**
     * 管理进程启动
     * @param Server|HttpServer|WsServer $server
     */
    public function onManagerStart($server): void
    {
    }

    /**
     * 管理进程结束
     * @param Server|HttpServer|WsServer $server
     */
    public function onManagerStop($server): void
    {
    }

    /**
     * 工作进程启动（Worker/Task）
     * @param Server|HttpServer|WsServer $server
     * @param int                        $workerId
     */
    public function onWorkerStart($server, int $workerId): void
    {
        if ($server->taskworker) {
            return;
        }

        // All Redis connections: [4 workers * 5 = 20, 4 workers * 20 = 80]
        $redisPool = new \Smf\ConnectionPool\ConnectionPool(
            [
                'minActive' => 5,
                'maxActive' => 20,
            ],
            new PhpRedisConnector,
            [
                'host'     => $this->config['host'],
                'port'     => $this->config['port'],
                'database' => $this->config['select'],
                'password' => $this->config['password'],
                'timeout'  => $this->config['timeout'],
            ]);
        $redisPool->init();
        $this->addConnectionPool('redis', $redisPool);
    }

    /**
     * 工作进程终止（Worker/Task）
     * @param Server|HttpServer|WsServer $server
     * @param int                        $workerId
     */
    public function onWorkerStop($server, int $workerId): void
    {
        $this->closeConnectionPools();
    }

    /**
     * 工作进程异常（Worker/Task）
     * @param Server|HttpServer|WsServer $server
     * @param int                        $workerId
     * @param int                        $workerPid
     * @param int                        $exitCode
     * @param int                        $signal
     */
    public function onWorkerError($server, int $workerId, int $workerPid, int $exitCode, int $signal): void
    {
        $this->closeConnectionPools();
    }

    /**
     * 工作进程收到消息
     * @param Server|HttpServer|WsServer $server
     * @param int                        $srcWorkerId
     * @param mixed                      $message
     */
    public function onPipeMessage($server, int $srcWorkerId, $message): void
    {
    }
}
