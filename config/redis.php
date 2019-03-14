<?php
/**
 * Created by PhpStorm.
 * User: NHZEXG
 * Date: 2018/12/4
 * Time: 19:20
 */

return [
    'host'         => think\facade\Env::get('redis.host', '127.0.0.1'), // redis主机
    'port'         => think\facade\Env::get('redis.port', 6379), // redis端口
    'password'     => think\facade\Env::get('redis.password', ''), // 密码
    'select'       => think\facade\Env::get('redis.select', 0), // 操作库
    'timeout'      => think\facade\Env::get('redis.timeout', 3), // 超时时间(秒)
    'persistent'   => think\facade\Env::get('redis.persistent', false) ? true : false, // 是否长连接
];