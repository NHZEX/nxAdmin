<?php

use Tp\Log\Driver\Socket;

// +----------------------------------------------------------------------
// | 日志设置
// +----------------------------------------------------------------------

return [
    // 默认日志记录通道
    'default'      => env_get('LOG_CHANNEL', 'file'),
    // 日志记录级别
    'level'        => [],
    // 日志类型记录的通道 ['error'=>'email',...]
    'type_channel' => [],
    // 关闭全局日志写入
    'close'        => false,
    // 全局日志处理 支持闭包
    'processor'    => null,
    // 日志通道列表
    'channels'     => [
        'file' => [
            // 日志记录方式
            'type'           => 'File',
            // 日志保存目录
            'path'           => env_get('LOG_FILE_PATH', runtime_path('log')),
            // 单文件日志写入
            'single'         => true,
            // 独立日志级别
            'apart_level'    => [],
            // 最大日志文件数量
            'max_files'      => env_get('LOG_FILE_MAX_FILES', 30),
            // 日志文件大小限制
            'file_size'      => env_get('LOG_FILE_FILE_SIZE', 4194304),
            // 使用JSON格式记录
            'json'           => false,
            // 日志处理
            'processor'      => null,
            // 关闭通道日志写入
            'close'          => false,
            // 日志输出格式化
            'format'         => '[%s][%s] %s',
            // 是否实时写入
            'realtime_write' => false,
        ],
        // 其它日志通道配置
        'remote' => [
            // 日志记录方式
            'type'           => Socket::class,
            // socket服务器地址
            'host'           => env_get('LOG_REMOTE_HOST', '127.0.0.1'),
            // 是否显示加载的文件列表
            'show_included_files' => false,
            // 日志强制记录到配置的 client_id
            'force_client_ids' => explode(',', env_get('LOG_REMOTE_FORCE_CLIENT', 'develop')),
            // 限制允许读取日志的 client_id
            'allow_client_ids' => explode(',', env_get('LOG_REMOTE_ALLOW_CLIENT', 'develop')),
            // 日志处理
            'processor'      => null,
            // 关闭通道日志写入
            'close'          => false,
            // 日志输出格式化
            'format'         => '[%s][%s] %s',
            // 是否实时写入
            'realtime_write' => false,
        ],
    ],
];
