<?php

// +----------------------------------------------------------------------
// | 日志设置
// +----------------------------------------------------------------------

use think\App;

// 格式日志头
$formatHead = function ($uir, App $app) {
    $runtime     = round(microtime(true) - $app->getBeginTime(), 10);
    $time_str    = ' [运行时间：' . number_format($runtime, 6) . 's]';
    $memory_use  = number_format((memory_get_usage() - $app->getBeginMem()) / 1024, 2);
    $memory_peak = number_format(memory_get_peak_usage() / 1024, 2);
    $memory_str  = ' [内存消耗：' . $memory_use . 'kb，峰值：' . $memory_peak . 'kb]';
    $file_load   = ' [文件加载：' . count(get_included_files()) . ']';
    return $uir  . $time_str . $memory_str . $file_load;
};

return [
    // 默认日志记录通道
    'default'      => env('LOG_CHANNEL', 'file'),
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
            'path'           => env('LOG_FILE_PATH', runtime_path('log')),
            // 单文件日志写入
            'single'         => true,
            // 独立日志级别
            'apart_level'    => [],
            // 最大日志文件数量
            'max_files'      => env('LOG_FILE_MAX_FILES', 30),
            // 日志文件大小限制
            'file_size'      => env('LOG_FILE_FILE_SIZE', 4194304),
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
            'type'           => 'socket',
            // 服务器地址
            'host'           => env('LOG_REMOTE_HOST', '127.0.0.1'),
            // 服务器端口
            'port'           => env('LOG_REMOTE_PORT', 1116),
            // 是否显示加载的文件列表
            'show_included_files' => false,
            // 日志强制记录到配置的 client_id
            'force_client_ids' => explode(',', env('LOG_REMOTE_FORCE_CLIENT', 'develop')),
            // 限制允许读取日志的 client_id
            'allow_client_ids' => explode(',', env('LOG_REMOTE_ALLOW_CLIENT', 'develop')),
            // 日志处理
            'processor'      => null,
            // 关闭通道日志写入
            'close'          => false,
            // 日志输出格式化
            'format'         => '[%s][%s] %s',
            // 是否实时写入
            'realtime_write' => false,
            // 默认展开节点
            'expand_level'   => ['debug'],
            // 自定义日志头
            'format_head'    => $formatHead,
        ],
    ],
];
