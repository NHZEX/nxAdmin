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
// | 控制台配置
// +----------------------------------------------------------------------
use app\Command\Certificate;
use app\Command\CreateModel;
use think\facade\Env;

return [
    // 执行用户（Windows下无效）
    'user'     => Env::get('task.user', null),
    // 指令定义
    'commands' => [
        Certificate::class,
        CreateModel::class,
    ],
];
