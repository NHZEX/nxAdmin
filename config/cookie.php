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
// | Cookie设置
// +----------------------------------------------------------------------

$mixing_prefix = \app\server\Deploy::getMixingPrefix();

return [
    // cookie 名称前缀
    'prefix'    => $mixing_prefix ? "{$mixing_prefix}_" : '',
    // cookie 保存时间
    'expire'    => 0,
    // cookie 保存路径
    'path'      => '/',
    // cookie 有效域名
    'domain'    => '',
    //  cookie 启用安全传输
    'secure'    => false,
    // httponly设置
    'httponly'  => false,
    // 是否使用 setcookie
    'setcookie' => true,
];
