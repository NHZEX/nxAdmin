<?php
/**
 * Created by PhpStorm.
 * Date: 2019/1/5
 * Time: 10:54
 */

use app\Service\Auth\Middleware\AllowCrossDomain;
use app\Service\Auth\Middleware\SessionInit;

return [
    AllowCrossDomain::class,
    SessionInit::class,
    // 全局请求缓存
    // \think\middleware\CheckRequestCache::class,
    // 多语言加载
    // \think\middleware\LoadLangPack::class,
];
