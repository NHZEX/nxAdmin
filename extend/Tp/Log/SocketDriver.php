<?php
declare(strict_types=1);

namespace Tp\Log;

use think\log\driver\Socket;
use function curl_exec;
use function curl_init;
use function curl_setopt;
use function strlen;
use function zlib_encode;

class SocketDriver extends Socket
{
    protected function send($host, $port, $message = '', $address = '/')
    {
        $url = "http://{$host}:{$port}{$address}";
        $ch  = curl_init();

        $headers = [];
        if ($this->config['compress'] ?? false && strlen($message) > 128) {
            $message = zlib_encode($message, ZLIB_ENCODING_DEFLATE);
            $headers[] = 'Content-Type: application/x-compress';
        } else {
            $headers[] = 'Content-Type: application/json; charset=UTF-8';
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $message);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); //设置header

        return curl_exec($ch);
    }
}
