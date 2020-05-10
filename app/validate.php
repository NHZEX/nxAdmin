<?php

use app\controller;
use app\Validate as Validate;

return [
    controller\api\admin\Index::class => [
        'login' => [Validate\Login::class],
    ],

    controller\api\admin\User::class => [
        'save'   => [Validate\Admin\User::class, 'save'],
        'update' => [Validate\Admin\User::class, 'update'],
    ],
    controller\api\admin\Role::class => [
        'save'   => [Validate\Admin\Role::class, null],
        'update' => [Validate\Admin\Role::class, null],
    ],
];
