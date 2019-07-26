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

use app\Server\DeployInfo;
use Tp\Session\Driver\Redis;

return [
    // SESSION COOKIN
    'name'           => 'one',
    // SESSION_ID的提交变量,解决flash上传跨域
    'var_session_id' => '',
    // 驱动方式 支持redis memcache memcached
    'type'           => Redis::class,
    // SESSION 超时(2小时)
    'expire'         => (int) env_get('SESSION_EXPIRE', 7200),
    // Memcached And Redis SESSION KEY 的前缀
    'prefix'   => DeployInfo::getMixingPrefix() . ':sess:',
    // REDIS 设置
    'host'         => env_get('SESSION_REDIS_HOST', '127.0.0.1'), // redis主机
    'port'         => (int) env_get('SESSION_REDIS_PORT', 6379), // redis端口
    'password'     => env_get('SESSION_REDIS_PASSWORD', ''), // 密码
    'select'       => (int) env_get('SESSION_REDIS_SELECT', 0), // 操作库
    'timeout'      => (int) env_get('SESSION_REDIS_TIMEOUT', 3), // 超时时间(秒)
    'persistent'   => (bool) env_get('SESSION_REDIS_PERSISTENT', false), // 是否长连接
];
