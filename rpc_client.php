<?php

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/RpcTest.php';

use HZEX\SimpleRpc\Exception\RpcRemoteExecuteException;
use HZEX\SimpleRpc\Observer\ClientHandleInterface;
use HZEX\SimpleRpc\Protocol\TransferFrame;
use HZEX\SimpleRpc\RpcClient;
use HZEX\SimpleRpc\RpcProvider;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Swoole\Coroutine;
use Swoole\Timer;
use function Swoole\Coroutine\run;

class TestRpcCilent implements ClientHandleInterface
{

    protected $logger;
    protected $client;

    /**
     * @var RpcTest
     */
    protected $test;
    protected $loop;

    public function __construct() {
        $this->logger = new Logger('test');
        $this->logger->pushHandler(new StreamHandler('php://stdout'));

        $provider     = new RpcProvider();
        $this->client = new RpcClient($this, $this->logger);
        $this->client->connect($provider, '127.0.0.1', 9506);
        $this->startCleanRpc();
    }

    protected function startCleanRpc() {
        Timer::tick(5000, function () {
            $terminal = $this->client->getTerminal();
            $this->logger->debug("RPC_GC: {$terminal->gcTransfer()}/{$terminal->countTransfer()}");
            $this->logger->debug("RPC_IH: {$terminal->countInstanceHosting()}");
        });
    }

    /**
     */
    public function onConnect(): void
    {
        go(function () {
            do {
                try {
                    $this->test = RpcTest::new();
                    $this->startLoop();
                    break;
                } catch (RpcRemoteExecuteException $e) {
                    $errMsg = "instance dataMonitor fail: {$e->getCode()}, {$e->getMessage()}, {$e->getRemoteTrace()}";
                    $this->logger->error($errMsg);
                }
                Coroutine::sleep(1);
            } while ($this->client->isConnected());
        });
    }

    protected function startLoop()
    {
        $this->loop = Timer::tick(1000, Closure::fromCallable([$this, 'trigger']));
    }

    protected function trigger()
    {
        $result = $this->test->index('hello world', time());
        $this->logger->info('results: ' . $result);
    }

    /**
     * 停止拉取健康数据
     */
    private function stopLoop()
    {
        if ($this->loop && Timer::exists($this->loop)) {
            Timer::clear($this->loop);
        }
    }

    /**
     */
    public function onClose(): void
    {
        $this->stopLoop();
    }

    /**
     * @param string $data
     * @return bool|null
     */
    public function onReceive(string $data): ?bool
    {
        return false;
    }

    /**
     * @param TransferFrame $frame
     * @return bool|null
     */
    public function onSend(TransferFrame $frame): ?bool
    {
        return false;
    }
}

run(function () {
    new TestRpcCilent();
});