<?php
// +----------------------------------------------------------------------
// | 控制台配置
// +----------------------------------------------------------------------

use app\Command\CreateModel;
use Zxin\Think\Model\ModelGenerator\Command\ModelToolCommand;

return [
    // 执行用户（Windows下无效）
    'user'     => env('COMMAND_USER', null),
    // 指令定义
    'commands' => [
        CreateModel::class,
        ModelToolCommand::class,
    ],
];
