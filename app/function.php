<?php

declare(strict_types=1);

use think\event\HttpEnd;

/**
 * 项目自定义全局函数文件
 * 建议使用类静态方法或者带命名空间的函数声明.
 */
function is_cli(): bool
{
    return 'cli' === \PHP_SAPI;
}
function get_temp_filename_with_auto_clear(string $prefix, string $extension = ''): string
{
    static $fileList = null;
    static $dirPrefix = null;
    if (null === $fileList) {
        $fileList = [];
        app()->event->listen(HttpEnd::class, static function () use (&$fileList): void {
            foreach ($fileList as $filename) {
                @unlink($filename);
            }
        });
    }
    $dirPrefix ??= env('DEPLOY_MIXING_PREFIX');
    $dir = sys_get_temp_dir();
    if ($dirPrefix) {
        $dir .= \DIRECTORY_SEPARATOR.$dirPrefix;
    }
    if (!is_dir($dir)) {
        mkdir($dir);
    }
    $filename = $dir.\DIRECTORY_SEPARATOR.'p_'.getmypid().'_'.$prefix.'_'.dechex(time()).bin2hex(random_bytes(8));
    if ($extension) {
        $filename .= ".{$extension}";
    }
    $fileList[] = $filename;

    return $filename;
}

function json_encode_ex(mixed $value, int $flags = 0, int $depth = 512): string
{
    $flags |= \JSON_UNESCAPED_UNICODE;
    $flags |= \JSON_UNESCAPED_SLASHES;
    $flags |= \JSON_THROW_ON_ERROR;

    return json_encode($value, $flags, $depth);
}

function json_decode_ex(string $json, bool $associative = true, int $depth = 512, int $flags = 0): mixed
{
    $flags |= \JSON_THROW_ON_ERROR;

    return json_decode($json, $associative, $depth, $flags);
}

function cache_prefix(): string
{
    return env('DEPLOY_MIXING_PREFIX');
}

function global_security_salt(): string
{
    return env('DEPLOY_SECURITY_SALT');
}

function global_disabled_remote_log(): void
{
    app()->log->close('remote');
}
