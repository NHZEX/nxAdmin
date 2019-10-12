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

use think\facade\Route;
use think\Response;

Route::get('upload', function () {
    return Response::create('404 Not Found', 'html', 404);
});
Route::get('static', function () {
    return Response::create('404 Not Found', 'html', 404);
});
Route::get('storage', function () {
    return Response::create('404 Not Found', 'html', 404);
});

Route::group('admin.login', function () {
    Route::get('captcha/:_', 'captcha');
})->prefix('admin.login/');

return [];
