<?php
# 优先级最高, 请谨慎使用助手函数

use think\App;

// 阻止phpunit(~9.3.0)加载全局函数，修复预加载失败
define('__PHPUNIT_GLOBAL_ASSERT_WRAPPERS__', false);

/**
 * 获取环境变量值
 * @param string|null $name
 * @param mixed       $default
 * @return string|int|bool|array<string, mixed>|null
 */
function env(string $name = null, $default = null)
{
    if (App::getInstance()->exists('env')) {
        return App::getInstance()->env->get($name, $default);
    } else {
        throw new RuntimeException('env instance not loaded');
    }
}
