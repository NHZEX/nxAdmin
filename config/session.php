<?php
// +----------------------------------------------------------------------
// | 会话设置
// +----------------------------------------------------------------------

use think\session\driver\Cache;

return [
    // session name
    'name'           => 'sess',
    // SESSION_ID的提交变量,解决flash上传跨域
    'var_session_id' => '',
    // 驱动方式 支持file cache
    'type'           => Cache::class,
    // 存储连接标识 当type使用cache的时候有效
    'store'          => 'session',
    // SESSION 超时(2小时)
    'expire'         => (int) env('SESSION_EXPIRE', 7200),
    // 前缀
    'prefix'         => '',
    // Cookie
    'cookie'         => [
        'httponly' => true,
    ],
];
