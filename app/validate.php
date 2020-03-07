<?php
/**
 * Created by PhpStorm.
 * User: Auoor
 * Date: 2019/3/31
 * Time: 10:33
 */

use app\controller;
use app\Validate as Validate;

return [
    controller\api\admin\Index::class => [
        'login' => [false, Validate\Login::class],
    ],

    controller\api\admin\Users::class => [
        'save'   => [false, Validate\Admin\User::class, 'save'],
        'update' => [false, Validate\Admin\User::class, 'update'],
    ],
    controller\api\admin\Roles::class => [
        'save'   => [false, Validate\Admin\Role::class, null],
        'update' => [false, Validate\Admin\Role::class, null],
    ],
];
