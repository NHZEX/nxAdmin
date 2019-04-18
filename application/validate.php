<?php
/**
 * Created by PhpStorm.
 * User: Auoor
 * Date: 2019/3/31
 * Time: 10:33
 */

use app\controller;
use app\Validate as validator;

return [
    controller\admin\Login::class => [
        'login' => [false, validator\Login::class],
    ],

    controller\admin\Manager::class => [
        'pageedit' => [false, validator\Manager::class, 'pageedit'],
        'save' => [true, validator\Manager::class, '?'],
        'delete' => [true, validator\Manager::class, 'delete'],
    ],
    controller\admin\Role::class => [
        'save' => [true, null, null],
        'permission' => [false, validator\Role::class, 'toPermission'],
        'savepermission' => [false, validator\Role::class, 'permission'],
    ],
    // TODO 菜单验证器
];
