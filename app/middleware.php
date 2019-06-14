<?php
/**
 * Created by PhpStorm.
 * Date: 2019/1/5
 * Time: 10:54
 */

use app\Middleware\Authorize;
use app\Middleware\DebugInfo;
use app\Middleware\Exception;
use app\Middleware\Validate;
use think\middleware\SessionInit;

return [
    // CrossSiteRequest::class,
    DebugInfo::class,
    SessionInit::class,
    Authorize::class,
    Validate::class,
    Exception::class,
    // 全局请求缓存
    // 'think\middleware\CheckRequestCache',
    // 多语言加载
    // 'think\middleware\LoadLangPack',
    // Session初始化
    // 'think\middleware\SessionInit',
    // 页面Trace调试
    // 'think\middleware\TraceDebug',
];
