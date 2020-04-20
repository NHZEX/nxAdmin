<?php

use app\controller;
use app\Validate as Validate;

return [
    controller\api\admin\Index::class => [
        'login' => [false, Validate\Login::class],
    ],

    controller\api\admin\User::class => [
        'save'   => [false, Validate\Admin\User::class, 'save'],
        'update' => [false, Validate\Admin\User::class, 'update'],
    ],
    controller\api\admin\Role::class => [
        'save'   => [false, Validate\Admin\Role::class, null],
        'update' => [false, Validate\Admin\Role::class, null],
    ],
];
