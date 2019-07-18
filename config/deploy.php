<?php

return [
    'db' => [
        'default' => 'main',
        'prefix' => 'DB',
        'item' => [
            'main' => [
                'desc' => '主数据库',
                'conf' => [
                    'hostname' => ['127.0.0.1', '地址', 'text', 'require|ip'],
                    'hostport' => [3306, '端口', 'int', 'integer|between:1,65535'],
                    'database' => ['db', '库名', 'text', 'require'],
                    'username' => ['root', '用户', 'text', 'require'],
                    'password' => ['', '密码', 'text', null],
                    'debug' => [false, '调试', null, null],
                ],
            ],
        ],
    ],
    'redis' => [
        'host' => ['127.0.0.1', '地址', 'text', 'require|ip'],
        'port' => [6379, '端口', 'int', 'integer|between:1,65535'],
        'password' => ['', '密码', 'text', null],
        'select' => [0, '库名', 'int', 'integer|egt:0'],
        'timeout' => [5, '超时', 'int', 'integer|egt:0'],
        'persistent' => [false, '长连接', null, null],
    ],
    'cache' => [
        'prefix' => 'CACHE',
        'item' => [
            'redis' => [
                'desc' => '主缓存',
                'conf' => [
                    'host' => ['127.0.0.1', '地址', 'text', 'require|ip'],
                    'port' => [6379, '端口', 'int', 'integer|between:1,65535'],
                    'password' => ['', '密码', 'text', null],
                    'select' => [0, '库名', 'int', 'integer|egt:0'],
                    'timeout' => [5, '超时', 'int', 'integer|egt:0'],
                    'persistent' => [false, '长连接', null, null],
                ],
            ],
        ]
    ],
    'session' => [
        'expire' => [7200, '会话超时', 'int', 'require|integer'],
        'redis_host' => ['127.0.0.1', 'Redis主机', 'text', 'require|ip'],
        'redis_port' => [6379, 'Redis端口', 'int', 'integer|between:1,65535'],
        'redis_password' => ['', 'Redis密码', 'text', null],
        'redis_select' => [0, 'Redis库名', 'int', 'integer|egt:0'],
        'redis_timeout' => [5, 'Redis超时', 'int', 'integer|egt:0'],
        'redis_persistent' => [false, 'Redis长连接', null, null],
    ],
    'log' => [
        'default' => 'file',
        'prefix' => 'LOG',
        'item' => [
            'file' => [
                'desc' => '文件日志',
                'conf' => [
                    'path' => ['', '存储位置', 'text', 'writable'],
                    'max_files' => [30, '最大数量', 'int', 'integer|egt:0'],
                    'file_size' => [4194304, '文件大小', 'int', 'integer|egt:0'],
                ],
            ],
            'remote' => [
                'desc' => '远程日志',
                'conf' => [
                    'host' => ['', '服务主机', 'text', 'writable'],
                    'force_client' => [30, '强制记录', 'text', null],
                    'allow_client' => [4194304, '允许读取', 'text', null],
                ],
            ]
        ]
    ],
];
