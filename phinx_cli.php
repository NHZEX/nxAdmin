#!/usr/bin/env php
<?php
/**
 * Created by PhpStorm.
 * User: NHZEXG
 * Date: 2019/1/25
 * Time: 9:44
 */
require __DIR__ . '/thinkphp/base.php';

// 应用初始化
/** @var \think\App $app */
$app = think\Container::get('app');
$app->path(__DIR__ . '/app/')->initialize();

// Phinx初始化
$phinxApp = new Phinx\Console\PhinxApplication();
/** @noinspection PhpUnhandledExceptionInspection */
$phinxApp->run();
