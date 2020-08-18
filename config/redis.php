<?php

return [
    // 默认使用的数据库连接配置
    'default' => 'main',
    // 数据库连接配置信息
    'connections' => [
        'main' => [
            'host'         => env('REDIS_HOST', '127.0.0.1'), // redis主机
            'port'         => (int) env('REDIS_PORT', 6379), // redis端口
            'password'     => env('REDIS_PASSWORD', ''), // 密码
            'select'       => (int) env('REDIS_SELECT', 0), // 操作库
            'timeout'      => (int) env('REDIS_TIMEOUT', 3), // 超时时间(秒)
            'persistent'   => (bool) env('REDIS_PERSISTENT', false), // 是否长连接
            'pool'         => [
                'min_active'    => 0,
                'max_active'    => 8,
                'max_wait_time' => 5,
                'max_idle_time' => 60,
                'idle_check_interval' => 120,
            ],
            'fast_freed' => true,
            'auto_discard' => false,
        ],
        'session' => [
            'host'         => env('SESSION_REDIS_HOST', '127.0.0.1'),
            'port'         => (int) env('SESSION_REDIS_PORT', 6379),
            'password'     => env('SESSION_REDIS_PASSWORD', ''),
            'select'       => (int) env('SESSION_REDIS_SELECT', 0),
            'timeout'      => (int) env('SESSION_REDIS_TIMEOUT', 3),
            'persistent'   => (bool) env('SESSION_REDIS_PERSISTENT', false),
            'pool'         => [
                'min_active'    => 0,
                'max_active'    => 8,
                'max_wait_time' => 5,
                'max_idle_time' => 60,
                'idle_check_interval' => 120,
            ],
            'fast_freed' => true,
            'auto_discard' => false,
        ],
    ],
];
