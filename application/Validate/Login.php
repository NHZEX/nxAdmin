<?php
/**
 * Created by PhpStorm.
 * Date: 2018/7/30
 * Time: 16:08
 */

namespace app\Validate;

class Login extends Base
{
    protected $rule = [
        '#' => 'require|length:16,32',
        'account' => 'require|length:4,64',
        'password' => 'require|length:4,64',
        'lasting' => 'boolean',
    ];

    // 验证提示信息
    protected $message = [
        '#' => '无效提交',
    ];

    // 验证字段描述
    protected $field = [
        'account' => '账号',
        'password' => '密码',
        'lasting' => '记住登陆',
    ];
}
