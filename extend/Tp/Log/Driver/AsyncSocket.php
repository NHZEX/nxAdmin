<?php
declare(strict_types=1);

namespace Tp\Log\Driver;

use Swoole\Coroutine;
use Swoole\Coroutine\Http\Client;
use Tp\Log\SocketDriver;
use function date;
use function file_put_contents;
use function runtime_path;
use function safe_get_coroutine_id;
use function sprintf;
use function strlen;
use function zlib_encode;

final class AsyncSocket extends SocketDriver
{
    /** @var Coroutine\Channel */
    private static $workerChannel;
    /** @var int */
    private static $workerId;

    /**
     * @access protected
     * @param string $host    - $host of socket server
     * @param int    $port    - $port of socket server
     * @param string $message - 发送的消息
     * @param string $address - 地址
     * @return bool
     */
    protected function send($host, $port, $message = '', $address = '/')
    {
        if (safe_get_coroutine_id() === -1) {
            return parent::send($host, $port, $message, $address);
        } else {
            return $this->asyncSend($host, $port, $message, $address);
        }
    }

    protected function asyncSend($host, $port, $message = '', $address = '/')
    {
        if (AsyncSocket::$workerChannel === null) {
            $this->worker($host, $port);
        }
        AsyncSocket::$workerChannel->push([$address, $message]);
        return true;
    }

    private function worker($host, $port)
    {
        AsyncSocket::$workerChannel = new Coroutine\Channel(1);
        $client = new Client($host, $port);
        $client->set([
            'timeout' => 10,
            'keep_alive' => true,
        ]);
        AsyncSocket::$workerId = Coroutine::create(function () use ($client) {
            while (true) {
                [$address, $message] = AsyncSocket::$workerChannel->pop();
                $this->sendData($client, $address, $message);
            }
        });
    }

    protected function sendData(Client $client, string $address, string $message)
    {
        $headers = [];
        if ($this->config['compress'] ?? false && strlen($message) > 128) {
            $message = zlib_encode($message, ZLIB_ENCODING_DEFLATE);
            $headers['Content-Type'] = 'application/x-compress';
        } else {
            $headers['Content-Type'] = 'application/json; charset=UTF-8';
        }
        $client->setHeaders($headers);
        $client->post($address, $message);
        if ($client->errCode !== 0) {
            $log = sprintf("[%s] send(%s): %s\n", date('Y-m-dTH:i:s'), $address, $client->errMsg);
        } elseif ($client->getStatusCode() !== 200) {
            $log = sprintf(
                "[%s] send(%s): httpCode %s\n",
                date('Y-m-dTH:i:s'),
                $address,
                $client->getStatusCode()
            );
        }
        if (isset($log)) {
            file_put_contents(runtime_path() . 'socklog_send_err.log', $log, FILE_APPEND);
        }
    }
}
