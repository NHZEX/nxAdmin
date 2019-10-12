<?php

use think\facade\Env;

return [
    'host'         => Env::get('REDIS_HOST', '127.0.0.1'), // redis主机
    'port'         => (int) Env::get('REDIS_PORT', 6379), // redis端口
    'password'     => Env::get('REDIS_PASSWORD', ''), // 密码
    'select'       => (int) Env::get('REDIS_SELECT', 0), // 操作库
    'timeout'      => (int) Env::get('REDIS_TIMEOUT', 3), // 超时时间(秒)
    'persistent'   => (bool) Env::get('REDIS_PERSISTENT', false), // 是否长连接
];
