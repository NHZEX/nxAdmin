<?php

use Monolog\Logger;
use think\facade\App;
use think\facade\Env;

return [
    'socketlog' => [
        //是否开启远程日志
        'enable' => Env::get('remotelog.enable', false),
        //监听地址
        'host' => Env::get('remotelog.host', '127.0.0.1'),
        //监听用户
        'force_client_ids' => explode(",", Env::get('remotelog.force_client_id', [])),
    ],
    'file' => [
        //写入日志级别
        'level' => Logger::WARNING,
        //日志路径
        'path' => App::getRuntimePath() . 'log' . DIRECTORY_SEPARATOR
    ]
];