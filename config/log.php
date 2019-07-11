<?php

// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\facade\Env;
use Tp\Log\Driver\Socket;

// +----------------------------------------------------------------------
// | 日志设置
// +----------------------------------------------------------------------

$log_file_path = Env::get('LOG_STORAGE_PATH', '');
$default_channel = (bool) Env::get('REMOTELOG_ENABLEH', false) ? 'remotelog' : 'file';

return [
    // 默认日志记录通道
    'default'      => Env::get('LOG_CHANNEL', $default_channel),
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
            'path'           => empty($log_file_path) ? runtime_path('log') : $log_file_path,
            // 单文件日志写入
            'single'         => false,
            // 独立日志级别
            'apart_level'    => [],
            // 最大日志文件数量
            'max_files'      => 0,
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
        'remotelog' => [
            // 日志记录方式
            'type'           => Socket::class,
            // socket服务器地址
            'host'           => Env::get('REMOTELOG_HOST', '127.0.0.1'),
            // 是否显示加载的文件列表
            'show_included_files' => false,
            // 日志强制记录到配置的 client_id
            'force_client_ids' => explode(',', Env::get('REMOTELOG_FORCE_CLIENT_ID', 'develop')),
            // 限制允许读取日志的 client_id
            'allow_client_ids' => [],
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
