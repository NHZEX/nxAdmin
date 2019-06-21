#!/usr/bin/env php
<?php
/**
 * Created by PhpStorm.
 * User: NHZEXG
 * Date: 2019/1/25
 * Time: 9:44
 */

use think\App;

require __DIR__ . '/vendor/autoload.php';

// 应用初始化
$app = (new App())->initialize();

// Phinx初始化
$phinxApp = new Phinx\Console\PhinxApplication();
/** @noinspection PhpUnhandledExceptionInspection */
$phinxApp->run();
