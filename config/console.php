<?php
// +----------------------------------------------------------------------
// | 控制台配置
// +----------------------------------------------------------------------

use Zxin\Think\Model\ModelGenerator\Command\ModelToolCommand;

return [
    // 执行用户（Windows下无效）
    'user'     => env('COMMAND_USER', null),
    // 指令定义
    'commands' => [
        ModelToolCommand::class,
    ],
];
