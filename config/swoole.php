<?php

use app\Service\Swoole\ResetApp;
use app\Service\Swoole\ResetDb;
use app\Service\Swoole\SwooleEvent;
use think\swoole\websocket\socketio\Handler;
use think\swoole\websocket\socketio\Parser;

return [
    'server'     => [
        'host'      => env('SERV_HTTP_HOST', '127.0.0.1'), // 监听地址
        'port'      => env('SERV_HTTP_PORT', 80), // 监听端口
        'mode'      => SWOOLE_BASE, // 运行模式 默认为SWOOLE_PROCESS
        'sock_type' => SWOOLE_SOCK_TCP, // sock type 默认为SWOOLE_SOCK_TCP
        'options'   => [
            'pid_file'              => runtime_path() . 'swoole.pid',
            'log_file'              => runtime_path() . 'swoole.log',
            'daemonize'             => false,
            'worker_num'            => env('SERV_WORKER_NUM', 4),
            'task_worker_num'       => env('SERV_TASK_WORKER_NUM', 0),
            'enable_static_handler' => true,
            'document_root'         => root_path('public'),
            'package_max_length'    => 20 * 1024 * 1024,
            'buffer_output_size'    => 8 * 1024 * 1024,
            'user'                  => env('SERV_WORKER_USER', null),
            'group'                 => env('SERV_WORKER_GROUP', null),
        ],
    ],
    'event' => SwooleEvent::class,
    'websocket'  => [
        'enable'        => false,
        'handler'       => Handler::class,
        'parser'        => Parser::class,
        'ping_interval' => 25000,
        'ping_timeout'  => 60000,
        'room'          => [
            'type'  => 'table',
            'table' => [
                'room_rows'   => 4096,
                'room_size'   => 2048,
                'client_rows' => 8192,
                'client_size' => 2048,
            ],
            'redis' => [
                'host'          => '127.0.0.1',
                'port'          => 6379,
                'max_active'    => 3,
                'max_wait_time' => 5,
            ],
        ],
        'listen'        => [],
        'subscribe'     => [],
    ],
    'rpc'        => [
        'server' => [
            'enable'   => false,
            'port'     => 9000,
            'services' => [
            ],
        ],
        'client' => [
        ],
    ],
    'hot_update' => [
        'enable'  => env('APP_DEBUG', false),
        'name'    => ['*.php'],
        'include' => [app_path()],
        'exclude' => [],
    ],
    //连接池
    'pool'       => [
        'db'    => [
            'enable'        => true,
            'max_active'    => 8,
            'max_wait_time' => 10,
        ],
        'cache' => [
            'enable'        => false,
            'max_active'    => 3,
            'max_wait_time' => 5,
        ],
    ],
    'coroutine'  => [
        'enable' => true,
        'flags'  => SWOOLE_HOOK_ALL,
    ],
    'tables'     => [],
    'process'    => [
    ],
    //每个worker里需要预加载以共用的实例
    'concretes'  => [
        'auth.permission',
        'redis',
        'model.event',
    ],
    //重置器
    'resetters'  => [
        ResetApp::class,
        ResetDb::class,
    ],
    //每次请求前需要清空的实例
    'instances'  => [],
    //每次请求前需要重新执行的服务
    'services'   => [],
];
