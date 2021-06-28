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
use think\middleware\Throttle;
use think\Response;

$r = App::getInstance()->route;

// 重定义资源路由
$r->rest(ROUTE_DEFAULT_RESTFULL, true);

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
    $r->get('captcha', 'captcha')->middleware(Throttle::class, [
        'visit_rate' => App::getInstance()->config->get('captcha.throttle_rate', '60/m'),
    ]);
})->prefix('system/');

$r->group('api/admin', function () use ($r) {
    $r->post('login', 'login');
    $r->get('logout', 'logout');
    $r->get('user-info', 'userInfo');
})->prefix('admin.index/');

$r->group('api', function () use ($r) {
    $r->group('admin', function () use ($r) {
        $r->resource('users', 'user');
        $r->resource('roles', 'role');

        roule_resource('permission', 'permission', [
            'scan' => ['get', 'scan', 'scan'],
        ])->pattern([
            'id' => '[\w\.\-]+'
        ]);
    })->prefix('admin.')->pattern([
        'id' => '\d+',
        'name' => '\w+',
    ]);
});

return [];
