<?php

// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

use think\facade\App;
use think\Response;

$r = App::getInstance()->route;

// 重定义资源路由
$r->rest([
    'index'  => ['get', '', 'index'],          // 获取资源
    'select' => ['get', '/select', 'select'],  // 获取资源
    'read'   => ['get', '/<id>', 'read'],      // 获取资源
    'save'   => ['post', '', 'save'],          // 创建资源
    'update' => ['put', '/<id>', 'update'],    // 替换资源
//    'patch'  => ['patch', '/<id>', 'patch'],
    'delete' => ['delete', '/<id>', 'delete'], // 删除资源
], true);

$r->get('upload', function () {
    return Response::create('404 Not Found', 'html', 404);
});
$r->get('static', function () {
    return Response::create('404 Not Found', 'html', 404);
});
$r->get('storage', function () {
    return Response::create('404 Not Found', 'html', 404);
});

$r->group('api/system', function () use ($r) {
    $r->get('config', 'config');
    $r->get('sysinfo', 'sysinfo');
    $r->get('captcha', 'captcha');
})->prefix('api.system/');

$r->group('api/admin', function () use ($r) {
    $r->post('login', 'login');
    $r->get('logout', 'logout');
    $r->get('user-info', 'userInfo');
})->prefix('api.admin.index/');

$r->group('api', function () use ($r) {
    $r->group('admin', function () use ($r) {
        $r->resource('users', 'user');
        $r->resource('roles', 'role');

        roule_resource('permission', 'permission', [
            'scan' => ['get', 'scan', 'scan'],
        ])->pattern([
            'id' => '[\w\.\-]+'
        ]);
    })->prefix('api.admin.')->pattern([
        'id' => '\d+',
        'name' => '\w+',
    ]);
});

return [];
