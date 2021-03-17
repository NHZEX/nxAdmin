<?php

declare(strict_types=1);

namespace Tp\Log;

use think\log\driver\Socket;
use function curl_error;
use function curl_exec;
use function curl_init;
use function curl_setopt;
use function date;
use function file_put_contents;
use function runtime_path;
use function sprintf;
use function strlen;
use function zlib_encode;

class SocketDriver extends Socket
{
    /**
     * @param string $host
     * @param int    $port
     * @param string $message
     * @param string $address
     * @return bool|string
     */
    protected function send($host, $port, $message = '', $address = '/')
    {
        $url = "http://{$host}:{$port}{$address}";
        $ch  = curl_init();

        if (!isset($this->config['compress'])) {
            $this->config['compress'] = false;
        }
        $headers = [];
        if ($this->config['compress'] && strlen($message) > 128) {
            $message = zlib_encode($message, ZLIB_ENCODING_DEFLATE);
            $headers[] = 'Content-Type: application/x-compress';
        } else {
            $headers[] = 'Content-Type: application/json; charset=UTF-8';
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $message);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->config['curl_opt'][CURLOPT_CONNECTTIMEOUT] ?? 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->config['curl_opt'][CURLOPT_TIMEOUT] ?? 10);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); //设置header

        $result = curl_exec($ch);

        if ($result === false) {
            $log = sprintf("[%s] send(%s): %s\n", date('Y-m-dTH:i:s'), $address, curl_error($ch));
            file_put_contents(runtime_path() . 'socklog_send.log', $log, FILE_APPEND);
        }

        return $result;
    }
}
