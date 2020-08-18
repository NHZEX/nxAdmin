<?php
declare(strict_types=1);

namespace Tp\Log\Driver;

use Swoole\Coroutine;
use Swoole\Coroutine\Http\Client;
use think\log\driver\Socket;
use function curl_error;
use function curl_exec;
use function curl_init;
use function curl_setopt;
use function date;
use function file_put_contents;
use function runtime_path;
use function sprintf;

class AsyncSocket extends Socket
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
        if (Coroutine::getCid() === -1) {
            return $this->curlSend($host, $port, $message, $address);
        } else {
            return $this->asyncSend($host, $port, $message, $address);
        }
    }

    protected function asyncSend($host, $port, $message = '', $address = '/')
    {
        if (self::$workerChannel === null) {
            self::worker($host, $port);
        } else {
            self::$workerChannel->push([$address, $message]);
        }
        return true;
    }

    private static function worker($host, $port)
    {
        self::$workerChannel = new Coroutine\Channel(1);
        $client = new Client($host, $port);
        $client->set([
            'timeout' => 10,
            'keep_alive' => true,
        ]);
        self::$workerId = Coroutine::create(function () use ($client) {
            while (true) {
                [$address, $message] = self::$workerChannel->pop();
                $client->setHeaders([
                    'Content-Type' => 'application/json;charset=UTF-8',
                ]);
                $client->post($address, $message);
                if ($client->errCode !== 0) {
                    $log = sprintf("send log: %s\n  >> %s\n", date('Y-m-dTH:i:s'), $client->errMsg);
                } elseif ($client->getStatusCode() !== 200) {
                    $log = sprintf("send log: %s\n  >> httpCode: %s\n", date('Y-m-dTH:i:s'), $client->getStatusCode());
                }
                if (isset($log)) {
                    file_put_contents(runtime_path() . 'socklog_send_err.log', $log, FILE_APPEND);
                }
            }
        });
    }

    protected function curlSend($host, $port, $message = '', $address = '/')
    {
        $url = 'http://' . $host . ':' . $port . $address;
        $ch  = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $message);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $headers = [
            "Content-Type: application/json;charset=UTF-8",
        ];

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); //设置header

        $result = curl_exec($ch);
        if ($result === false) {
            $log = sprintf("send log fail: %s\n  >> %s\n", date('Y-m-dTH:i:s'), curl_error($ch));
            file_put_contents(runtime_path() . 'socklog_send.log', $log, FILE_APPEND);
        }

        return $result;
    }
}
