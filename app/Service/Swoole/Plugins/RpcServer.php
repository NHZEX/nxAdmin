<?php
declare(strict_types=1);

namespace app\Service\Swoole\Plugins;

use app\Server\Rpc\RpcTest;
use app\Service\Swoole\RpcHandle;
use Closure;
use Exception;
use HZEX\SimpleRpc\RpcProvider;
use HZEX\SimpleRpc\RpcTerminal;
use HZEX\TpSwoole\Contract\WorkerPluginContract;
use HZEX\TpSwoole\Manager;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Swoole\Server;
use Swoole\Timer;
use think\App;
use unzxin\zswCore\Contract\EventSubscribeInterface;
use unzxin\zswCore\Event;

/**
 * Rpc服务
 * Class RpcServer
 * @package app\Server\Swoole
 */
class RpcServer implements WorkerPluginContract, EventSubscribeInterface
{
    /**
     * @var \HZEX\SimpleRpc\RpcServer
     */
    private $rpc;
    /**
     * @var array
     */
    private $config;
    /**
     * @var Manager
     */
    private $manager;
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * RpcServer constructor.
     * @param App     $app
     * @param Manager $manager
     */
    public function __construct(App $app, Manager $manager)
    {
        $this->manager = $manager;
        $this->logger = $manager->getLogger();
        $this->config = $app->config->get('rpc');
        $this->handleConfig();

        $this->rpc = new \HZEX\SimpleRpc\RpcServer(new RpcHandle());

        $provider = new RpcProvider();
        $provider->bind('test', RpcTest::class);

        [$host, $port] = explode(':', $this->config['listen']);
        $this->rpc->listen($manager->getSwoole(), $manager->getEvent(), $provider, $host, (int) $port);

        // 处理Rpc异常
        $this->rpc->getTerminal()->setErrorHandle(Closure::fromCallable([$this, 'handleError']));

        // 存储Rpc终端到容器
        $manager->getSandbox()->getBaseApp()->instance(RpcTerminal::class, $this->rpc->getTerminal());
        $this->manager->getLogger()->info("Rpc Server started: <{$this->config['listen']}>");
    }

    /**
     * 插件是否就绪
     * @param Manager $manager
     * @return bool
     */
    public function isReady(Manager $manager): bool
    {
        return true;
    }

    /**
     * 插件准备启动
     * @param Manager $manager
     * @return bool
     */
    public function prepare(Manager $manager): bool
    {
        $manager->getSandbox()->addDirectInstances(RpcTerminal::class);
        $manager->getSandbox()->addDirectInstances(\HZEX\SimpleRpc\RpcServer::class);
        return true;
    }

    /**
     * 处理 Rpc 异常
     * @param Exception $e
     */
    protected function handleError(Exception $e)
    {
        $class = get_class($e);
        $this->logger->error("Rpc Exception: {$class}#{$e->getCode()}#{$e->getMessage()}");
        $this->manager->getExceptionRecord()->handleException($e);
        $this->manager->getOutput()->renderException($e);
    }

    /**
     * 处理运行配置
     */
    private function handleConfig()
    {
        if (false === strpos($this->config['listen'], ':')) {
            $this->config['listen'] .= ':9502';
        }
        [$host, $port] = explode(':', $this->config['listen']);
        if (false === filter_var($host, FILTER_VALIDATE_IP)) {
            throw new InvalidArgumentException("rpc server listen host invalid: {$host}");
        }
        if (false === ctype_digit($port) || $port > 65535 || 1 > $port) {
            throw new InvalidArgumentException("rpc server listen port invalid: {$port}");
        }
        if (false === is_array($this->config['provider'])) {
            throw new InvalidArgumentException("rpc server provider value invalid");
        }
    }

    /**
     * 订阅服务事件
     * @param Event $event
     */
    public function subscribe(Event $event): void
    {
        $event->onSwooleWorkerStart(Closure::fromCallable([$this, 'onStart']));
    }

    /**
     * @param Server $server
     */
    public function onStart(Server $server)
    {
        if ($server->taskworker) {
            return;
        }
        $isDebug = $this->manager->getOutput()->isDebug();
        $terminal = $this->rpc->getTerminal();

        Timer::tick(1000 * 10, function () use ($server, $isDebug, $terminal) {
            $gcCount = $terminal->gcTransfer();
            if ($isDebug) {
                $this->logger->debug("RPC_GC#{$server->worker_id}: {$gcCount}/{$terminal->countTransfer()}");
                $this->logger->debug("RPC_IH#{$server->worker_id}: {$terminal->countInstanceHosting()}");
            }
        });
    }
}
