<?php
// +----------------------------------------------------------------------
// | 缓存设置
// +----------------------------------------------------------------------

return [
    // 默认缓存驱动
    'default' => env('CACHE_DRIVER', 'file'),
    // 缓存连接方式配置
    'stores'  => [
        'file' => [
            // 驱动方式
            'type'       => 'File',
            // 缓存保存目录
            'path'       => runtime_path('cache'),
            // 缓存前缀
            'prefix'     => '',
            // 缓存有效期 0表示永久缓存
            'expire'     => 0,
            // 缓存标签前缀
            'tag_prefix' => 'tag:',
            // 序列化机制 例如 ['serialize', 'unserialize']
            'serialize'  => [],
        ],
        'redis' => [
            // 驱动方式
            'type'       => 'redis',
            // 缓存前缀
            'prefix'     => env('DEPLOY_MIXING_PREFIX') . ':cache:',
            // 缓存有效期 0表示永久缓存
            'expire'     => 0,
            // 缓存标签前缀
            'tag_prefix' => 'tag:',
            // 序列化机制 例如 ['serialize', 'unserialize']
            'serialize'  => [],
            // Redis Host
            'host'       => env('REDIS_HOST', '127.0.0.1'),
            // Redis Port
            'port'       => (int) env('REDIS_PORT', 6379),
            // Redis Password
            'password'   => env('REDIS_PASSWORD', ''),
            // Redis Select
            'select'     => (int) env('REDIS_SELECT', 0),
            // Redis Timeout
            'timeout'    => (int) env('REDIS_TIMEOUT', 3),
            // Redis Persistent
            'persistent' => (bool) env('REDIS_PERSISTENT', false),
        ],
        'session' => [
            // 驱动方式
            'type'       =>  'redis',
            // 缓存前缀
            'prefix'     => env('DEPLOY_MIXING_PREFIX') . ':sess:',
            // 缓存有效期 0表示永久缓存
            'expire'     => 0,
            // 缓存标签前缀
            'tag_prefix' => 'tag:',
            // 序列化机制 例如 ['serialize', 'unserialize']
            'serialize'  => ['\return_raw_value', '\return_raw_value'],
            // Redis Host
            'host'       => env('SESSION_REDIS_HOST', '127.0.0.1'),
            // Redis Port
            'port'       => (int) env('SESSION_REDIS_PORT', 6379),
            // Redis Password
            'password'   => env('SESSION_REDIS_PASSWORD', ''),
            // Redis Select
            'select'     => (int) env('SESSION_REDIS_SELECT', 0),
            // Redis Timeout
            'timeout'    => (int) env('SESSION_REDIS_TIMEOUT', 3),
            // Redis Persistent
            'persistent' => (bool) env('SESSION_REDIS_PERSISTENT', false),
        ]
        // 更多的缓存连接
    ],
];
