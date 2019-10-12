<?php
// +----------------------------------------------------------------------
// | 缓存设置
// +----------------------------------------------------------------------

use app\Server\DeployInfo;

return [
    // 默认缓存驱动
    'default' => env_get('cache.driver', 'file'),
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
            'prefix'     => DeployInfo::getMixingPrefix() . ':cache:',
            // 缓存有效期 0表示永久缓存
            'expire'     => 0,
            // 缓存标签前缀
            'tag_prefix' => 'tag:',
            // 序列化机制 例如 ['serialize', 'unserialize']
            'serialize'  => [],
            // Redis Host
            'host'       => env_get('REDIS_HOST', '127.0.0.1'),
            // Redis Port
            'port'       => (int) env_get('REDIS_PORT', 6379),
            // Redis Password
            'password'   => env_get('REDIS_PASSWORD', ''),
            // Redis Select
            'select'     => (int) env_get('REDIS_SELECT', 0),
            // Redis Timeout
            'timeout'    => (int) env_get('REDIS_TIMEOUT', 3),
            // Redis Persistent
            'persistent' => (bool) env_get('REDIS_PERSISTENT', false),
        ],
        'session' => [
            // 驱动方式
            'type'       =>  'redis',
            // 缓存前缀
            'prefix'     => DeployInfo::getMixingPrefix() . ':sess:',
            // 缓存有效期 0表示永久缓存
            'expire'     => 0,
            // 缓存标签前缀
            'tag_prefix' => 'tag:',
            // 序列化机制 例如 ['serialize', 'unserialize']
            'serialize'  => [],
            // Redis Host
            'host'       => env_get('SESSION_REDIS_HOST', '127.0.0.1'),
            // Redis Port
            'port'       => (int) env_get('SESSION_REDIS_PORT', 6379),
            // Redis Password
            'password'   => env_get('SESSION_REDIS_PASSWORD', ''),
            // Redis Select
            'select'     => (int) env_get('SESSION_REDIS_SELECT', 0),
            // Redis Timeout
            'timeout'    => (int) env_get('SESSION_REDIS_TIMEOUT', 3),
            // Redis Persistent
            'persistent' => (bool) env_get('SESSION_REDIS_PERSISTENT', false),
        ]
        // 更多的缓存连接
    ],
];
