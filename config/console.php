<?php
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
