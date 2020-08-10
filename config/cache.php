<?php
// +----------------------------------------------------------------------
// | 缓存设置
// +----------------------------------------------------------------------

use app\Service\Redis\Tp\CacheDriver as RedisCacheDriver;

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
            'type'       => RedisCacheDriver::class,
            // 缓存前缀
            'prefix'     => env('DEPLOY_MIXING_PREFIX') . ':cache:',
            // 缓存有效期 0表示永久缓存
            'expire'     => 0,
            // 缓存标签前缀
            'tag_prefix' => 'tag:',
            // 序列化机制 例如 ['serialize', 'unserialize']
            'serialize'  => [],
            // 连接名
            'connection' => 'main',
        ],
        'session' => [
            // 驱动方式
            'type'       =>  RedisCacheDriver::class,
            // 缓存前缀
            'prefix'     => env('DEPLOY_MIXING_PREFIX') . ':sess:',
            // 缓存有效期 0表示永久缓存
            'expire'     => 0,
            // 缓存标签前缀
            'tag_prefix' => 'tag:',
            // 序列化机制 例如 ['serialize', 'unserialize']
            'serialize'  => ['\return_raw_value', '\return_raw_value'],
            // 连接名
            'connection' => 'session',
        ]
        // 更多的缓存连接
    ],
];
