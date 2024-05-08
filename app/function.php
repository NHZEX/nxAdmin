<?php

use think\event\HttpEnd;

/**
 * 项目自定义全局函数文件
 * 建议使用类静态方法或者带命名空间的函数声明
 */

function is_cli(): bool
{
    return 'cli' === PHP_SAPI;
}

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

function json_encode_ex(mixed $value, int $flags = 0, int $depth = 512): string
{
    $flags |= JSON_UNESCAPED_UNICODE;
    $flags |= JSON_UNESCAPED_SLASHES;
    $flags |= JSON_THROW_ON_ERROR;
    return json_encode($value, $flags, $depth);
}

function json_decode_ex(string $json, bool $associative = true, int $depth = 512, int $flags = 0): mixed
{
    $flags |= JSON_THROW_ON_ERROR;
    return json_decode($json, $associative, $depth, $flags);
}
