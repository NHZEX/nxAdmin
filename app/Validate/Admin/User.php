<?php

declare(strict_types=1);

namespace app\Validate\Admin;

use app\Validate\Base;

class User extends Base
{
    // todo genre、status 从模型获取有效范围
    protected $rule = [
        'genre' => 'require|number',
        'status' => 'require|number',
        'role_id' => 'number',
        'role_ids' => 'array',
        'username' => 'require|length:3,64',
        'nickname' => 'require|length:3,64',
        'password' => 'require|length:4,64',
        'lock_version' => 'number',
    ];

    // 验证字段描述
    protected $field = [
        'password' => '密码',
        'username' => '账号',
        'nickname' => '昵称',
        'email' => '邮箱',
        'phone' => '手机',
        'role_id' => '角色ID',
    ];

    protected $scene = [
        'save' => [
            'genre', 'username', 'nickname', 'password', 'role_ids', 'status',
        ],
        'resetPasswod' => [
            'password',
        ],
    ];

    protected function sceneUpdate(): void
    {
        $this
            ->only(['nickname', 'role_id', 'role_ids', 'status', 'password'])
            ->remove('password', 'require');
    }
}
