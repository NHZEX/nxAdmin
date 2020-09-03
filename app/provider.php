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

use app\ExceptionHandle;
use app\Request;
use HZEX\Think\EnvLoader;
use Tp\Log\Log;
use Tp\Model\Event;

return [
    'env' => EnvLoader::class,
    'log' => Log::class,
    'think\Request' => Request::class,
    'think\exception\Handle' => ExceptionHandle::class,
    'model.event' =>  Event::class,
];
