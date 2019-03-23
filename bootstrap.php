<?php
/**
 * Created by PhpStorm.
 * User: NHZEXG
 * Date: 2018/7/4
 * Time: 10:01
 */

namespace think;

// ThinkPHP 引导文件
// 加载基础文件
require_once __DIR__ . '/thinkphp/base.php';

/** @var App $app */
$app = Container::get('app');
$app->path(__DIR__. '/application/');
$app->initialize();
$app->hook->listen('app_init');


Loader::addAutoLoadDir(__DIR__ . '/extend');
