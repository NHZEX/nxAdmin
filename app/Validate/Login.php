<?php

/**
 * Created by PhpStorm.
 * Date: 2018/7/30
 * Time: 16:08.
 */

namespace app\Validate;

class Login extends Base
{
    protected $rule = [
        'token' => 'min:16',
        'username' => 'require|length:4,64',
        'password' => 'require|length:4,64',
        'lasting' => 'boolean',
    ];

    // 验证提示信息
    protected $message = [
    ];

    // 验证字段描述
    protected $field = [
        'username' => '登录账号',
        'password' => '登录密码',
        'lasting' => '记住登陆',
    ];
}
