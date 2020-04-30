<?php

return [
    // 数据库配置
    'db' => [
        'main' => [
            'desc' => '主数据库',
            'type' => 'mysql',
            'form' => [
                'hostname' => ['127.0.0.1', '地址', 'text', 'require'],
                'hostport' => [3306, '端口', 'int', 'integer|between:1,65535'],
                'database' => ['db', '库名', 'text', 'require'],
                'username' => ['root', '用户', 'text', 'require'],
                'password' => ['', '密码', 'text', null],
                'debug' => [false, '调试', null, null],
            ],
        ],
    ],
    // Redis配置
    'redis' => [
        'host' => ['127.0.0.1', '地址', 'text', 'require'],
        'port' => [6379, '端口', 'int', 'integer|between:1,65535'],
        'password' => ['', '密码', 'text', null],
        'select' => [0, '库名', 'int', 'integer|egt:0'],
        'timeout' => [5, '超时', 'int', 'integer|egt:0'],
        'persistent' => [false, '长连接', null, null],
    ],
    // 会话配置
    'session' => [
        'expire' => [7200, '会话超时', 'int', 'require|integer'],
        'redis_host' => ['127.0.0.1', 'Redis主机', 'text', 'require'],
        'redis_port' => [6379, 'Redis端口', 'int', 'integer|between:1,65535'],
        'redis_password' => ['', 'Redis密码', 'text', null],
        'redis_select' => [0, 'Redis库名', 'int', 'integer|egt:0'],
        'redis_timeout' => [5, 'Redis超时', 'int', 'integer|egt:0'],
        'redis_persistent' => [false, 'Redis长连接', null, null],
    ],
    // 缓存配置-未使用
    'cache' => [
    ],
    // 日志配置-未使用
    'log' => [
        'file' => [
            'desc' => '文件日志',
            'form' => [
                'path' => ['', '存储位置', 'text', 'writable'],
                'max_files' => [30, '最大数量', 'int', 'integer|egt:0'],
                'file_size' => [4194304, '文件大小', 'int', 'integer|egt:0'],
            ],
        ],
        'remote' => [
            'desc' => '远程日志',
            'form' => [
                'host' => ['', '服务主机', 'text', 'writable'],
                'force_client' => [30, '强制记录', 'text', null],
                'allow_client' => [4194304, '允许读取', 'text', null],
            ],
        ]
    ],
];
