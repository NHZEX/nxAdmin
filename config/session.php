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

// +----------------------------------------------------------------------
// | 会话设置
// +----------------------------------------------------------------------

use think\Facade\Env;

$mixing_prefix = \app\server\Deploy::getMixingPrefix();

return [
    'id'             => '',
    // SESSION_ID的提交变量,解决flash上传跨域
    'var_session_id' => '',
    // SESSION 前缀
    'prefix'         => 'think',
    // 驱动方式 支持redis memcache memcached
    'type'           => \app\dirver\session\Redis2::class,
    // 是否自动开启 SESSION
    'auto_start'     => false,
    // 关闭自动生成Cookies
    'use_cookies'    => false,
    // SESSION COOKIN
    'name'           => 'one',
    // Memcached And Redis SESSION KEY 的前缀
    'session_name'   => $mixing_prefix ? "{$mixing_prefix}:" : 'session',
    // REDIS 设置
    'host'         => Env::get('redis.host', '127.0.0.1'), // redis主机
    'port'         => Env::get('redis.port', 6379), // redis端口
    'password'     => Env::get('redis.password', ''), // 密码
    'select'       => Env::get('redis.select', 0), // 操作库
    'timeout'      => Env::get('redis.timeout', 3), // 超时时间(秒)
    'persistent'   => Env::get('redis.persistent', false), // 是否长连接
];
