<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

use app\Service\Swoole\DestroyRedisConnection;
use app\Service\Swoole\ServiceHealthCheck;

return [
    'server'       => [
        'listen'    => env_get('SERVER_HTTP_LISTEN', '0.0.0.0:9501'), // 监听
        'mode'      => SWOOLE_PROCESS, // 运行模式 默认为SWOOLE_PROCESS
        'sock_type' => SWOOLE_TCP, // sock type 默认为SWOOLE_SOCK_TCP
        'options'   => [
            'daemonize'                => false,
            'dispatch_mode'            => 2,
            'worker_num'               => env_get('SERVER_WORKER_NUM', 4),
            'task_worker_num'          => env_get('SERVER_TASK_WORKER_NUM', 2),
            // 运行时文件
            'pid_file'                 => runtime_path() . 'swoole.pid',
            'log_file'                 => runtime_path() . 'swoole.log',
            // 启用Http响应压缩
            'http_compression'         => true,
            // 启用静态文件处理
            'enable_static_handler'    => true,
            // 设置静态文件根目录
            'document_root'            => root_path() . 'public',
            // 设置静态处理器的路径
            'static_handler_locations' => ['/static', '/upload', '/favicon.ico', '/robots.txt'],

            //心跳检测：每60秒遍历所有连接，强制关闭20分钟内没有向服务器发送任何数据的连接
            'heartbeat_check_interval' => 60,
            'heartbeat_idle_time'      => 1200,

            'package_max_length' => 20 * 1024 * 1024, // 设置最大数据包尺寸
            'buffer_output_size' => 10 * 1024 * 1024, // 发送输出缓存区内存尺寸
            'socket_buffer_size' => 128 * 1024 * 1024, // 客户端连接的缓存区长度

            // 'max_request' => 100000,
            // 'task_max_request' => 100000,
            'send_yield'         => true, // 发送数据协程调度
            'reload_async'       => true, // 异步安全重启
        ],
    ],
    'websocket'    => [
        'enabled'       => false,
        // 'handler' => WebSocket::class,
        // 'parser' => Parser::class,
        // 'route_file' => base_path() . 'websocket.php',
        'ping_interval' => 25000,
        'ping_timeout'  => 60000,
    ],
    'hot_reload'   => [
        'enable'  => env_get('SERVER_HOT_RELOAD', false),
        'name'    => ['*.php'],
        'notName' => [],
        'include' => [
            app_path(),
            root_path('extend'),
            root_path('vendor/topthink'),
            root_path('vendor/nhzex'),
        ],
        'exclude' => [],
    ],
    // 协程控制
    'coroutine'    => [
        'enable' => true,
        'flags'  => SWOOLE_HOOK_ALL,
    ],
    // 预加载实例（服务启动前执行）
    'concretes'    => [],
    // 重置器 (创建容器时执行)
    'resetters'    => [],
    // 清除实例 (创建容器时执行)
    'instances'    => [],
    // 自定义插件
    'plugins'      => [],
    // 自定义进程类
    'process'      => [],
    // 自定义任务类
    'tasks'        => [],
    // 事件定义类
    'events'       => [],
    // 上下文（容器）管理
    'container'    => [
        // 上下文销毁时要执行的操作
        'destroy' => [
            DestroyRedisConnection::class,
        ],
        // 共享实例 (允许容器间共享的实例，必须服务启动前创建的实例，可搭配预加载使用)
        'shared'  => [],
    ],
    // 监控监测实现
    'health'       => ServiceHealthCheck::class,
    // 运行内存限制
    'memory_limit' => '512M',
    // 追踪器 (调试)
    'tracker'      => env_get('SERVER_TRACKER', false),
    // 日志记录
    'log'          => [
        'console' => true,
        'channel' => [
            // 日志保存目录
            'path'      => env_get('LOG_FILE_PATH', '') ?: runtime_path('log'),
            // 日志文件名
            'filename'  => 'server.log',
            // 最大日志文件数量
            'max_files' => 7,
        ],
    ],
    // 连接池
    'pool'         => [
        'db'    => [
            'enable'        => true,
            'max_active'    => 3,
            'max_wait_time' => 5,
        ],
        'cache' => [
            'enable'        => true,
            'max_active'    => 3,
            'max_wait_time' => 5,
        ],
    ],
];
