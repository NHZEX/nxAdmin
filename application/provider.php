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
// 应用容器绑定定义

use app\server\RedisProxy;
use app\server\WebConv;
use think\App;
use think\Session2;

$basis = [
    'session'  => Session2::class,
    'redis' => RedisProxy::class,
    'web_conv' => WebConv::class,
];

$expand = [
];

foreach ($expand as $value) {
    App::getInstance()->bindTo($value);
}

return $basis;
