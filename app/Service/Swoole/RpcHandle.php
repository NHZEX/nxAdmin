<?php
declare(strict_types=1);

namespace app\Service\Swoole;

use HZEX\SimpleRpc\Observer\RpcHandleInterface;
use HZEX\SimpleRpc\Protocol\TransferFrame;
use HZEX\SimpleRpc\RpcServer;
use HZEX\SimpleRpc\Struct\Connection;

class RpcHandle implements RpcHandleInterface
{
    /**
     * @param RpcServer $server
     * @param int       $workerId
     */
    public function onWorkerStart(RpcServer $server, int $workerId): void
    {
    }

    /**
     * @param RpcServer $server
     */
    public function onWorkerStop(RpcServer $server): void
    {
    }

    /**
     * @param int        $fd
     * @param Connection $connection
     * @return bool|int
     */
    public function auth(int $fd, Connection $connection)
    {
        echo "join#$fd = $connection\n";
        return true;
    }

    /**
     * @param int        $fd
     * @param Connection $connection
     */
    public function onConnect(int $fd, Connection $connection): void
    {
        // ClientFdMapping::setFd($fd);
    }

    public function onClose(int $fd, Connection $connection): void
    {
        // ClientFdMapping::close($fd);
        echo "close#RpcHandle#$fd\n";
    }

    public function onReceive(int $fd, string $data, Connection $connection): ?bool
    {
        return false;
    }

    public function onSend(int $fd, TransferFrame $frame): ?bool
    {
        return false;
    }
}
