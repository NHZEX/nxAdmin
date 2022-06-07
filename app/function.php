<?php

use think\event\HttpEnd;

/**
 * 项目自定义全局函数文件
 * 建议使用类静态方法或者带命名空间的函数声明
 */
function get_temp_filename_with_auto_clear(string $prefix, string $extension = ''): string
{
    static $fileList = null;
    if (null === $fileList) {
        $fileList = [];
        app()->event->listen(HttpEnd::class, static function () use (&$fileList) {
            foreach ($fileList as $filename) {
                @unlink($filename);
            }
        });
    }
    $dir = sys_get_temp_dir();
    $filename = $dir . DIRECTORY_SEPARATOR . uniqid($prefix, true) . $extension;
    $fileList[] = $filename;
    return $filename;
}
