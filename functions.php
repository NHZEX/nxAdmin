<?php
# 优先级最高, 请谨慎使用助手函数

use think\App;
use Tp\Response\File;

// 阻止phpunit(~9.3.0)加载全局函数，修复预加载失败
define('__PHPUNIT_GLOBAL_ASSERT_WRAPPERS__', false);

/**
 * 获取环境变量值
 * @access public
 * @param string $name    环境变量名（支持二级 .号分割）
 * @param string $default 默认值
 * @return mixed
 */
function env(string $name = null, $default = null)
{
    if (App::getInstance()->exists('env')) {
        return App::getInstance()->env->get($name, $default);
    } else {
        throw new RuntimeException('env instance not loaded');
    }
}

/**
 * 获取\think\response\Download对象实例
 * @param string $filename 要下载的文件
 * @param string $name     显示文件名
 * @param bool   $content  是否为内容
 * @param int    $expire   有效期（秒）
 * @return File
 */
function download(string $filename, string $name = '', bool $content = false, int $expire = 180): File
{
    return (new File($filename))->name($name)->isContent($content)->expire($expire);
}
