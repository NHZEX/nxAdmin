<?php

declare(strict_types=1);

namespace app\Service\DebugHelper;

use Closure;
use Composer\InstalledVersions;
use function Zxin\Util\format_byte;
use const OPENSSL_VERSION_TEXT;
use const ZLIB_VERSION;

class EnvironmentHelper
{
    public static function getEnvInfo(): array
    {
        return [
            [
                'label' => 'sys_version',
                'name'  => '服务器系统',
                'value' => php_uname(),
            ],
            [
                'label' => 'php_version',
                'name'  => 'PHP版本',
                'value' => PHP_VERSION,
            ],
            [
                'label' => 'server_software',
                'name'  => '执行环境',
                'value' => sprintf(
                    '%s (%s)',
                    $_SERVER['SERVER_SOFTWARE'],
                    PHP_SAPI,
                ),
            ],
            [
                'label' => 'framework_info',
                'name'  => '系统框架',
                'value' => sprintf(
                    'topthink: %s; think-orm: %s',
                    InstalledVersions::getPrettyVersion("topthink/framework"),
                    InstalledVersions::getPrettyVersion("topthink/think-orm"),
                ),
            ],
            [
                'label' => 'db_version',
                'name'  => '数据库版本',
                'value' => db_version(null, true),
            ],
            [
                'label' => 'memory_limit',
                'name'  => '内存限制',
                'value' => \ini_get('memory_limit'),
            ],
            [
                'label' => 'max_execution_time',
                'name'  => '最长执行时间',
                'value' => \ini_get('max_execution_time'),
            ],
            [
                'label' => 'upload_max_filesize',
                'name'  => '上传限制',
                'value' => \ini_get('upload_max_filesize'),
            ],
            [
                'label' => 'post_max_size',
                'name'  => 'POST限制',
                'value' => \ini_get('post_max_size'),
            ],
            [
                'label' => 'realpath_cache_size',
                'name'  => '路径缓存',
                'value' => realpath_cache_size(),
            ],
            [
                'label' => 'main extension',
                'name'  => '主要扩展',
                'value' => EnvironmentHelper::mainExtension(),
            ],
            [
                'label' => 'opcache_info',
                'name'  => 'opcache',
                'value' => EnvironmentHelper::opcacheInfo(),
            ],
            [
                'label' => 'debug_env',
                'name'  => '调试环境',
                'value' => EnvironmentHelper::xdebugInfo(),
            ],
        ];
    }

    public static function opcacheInfo(): string|array
    {
        if (!\extension_loaded('Zend OPcache') || !\function_exists('opcache_get_status')) {
            return 'opcache not active';
        }
        $status = opcache_get_status(false);
        if (!$status['opcache_enabled']) {
            return 'opcache off';
        }
        $memory           = $status['memory_usage'];
        $interned_strings = $status['interned_strings_usage'];
        $statistics       = $status['opcache_statistics'];
        $jit              = $status['jit'] ?? null;

        $jitInfo = ($jit['enabled'] ?? false) && ($jit['on'] ?? false) ? sprintf(
            "buffer: %s / %s, mode: %s, kind: %d, opt_level: %d, opt_flags: %d;",
            format_byte($jit['buffer_free']),
            format_byte($jit['buffer_size']),
            \ini_get('opcache.jit') ?? 'unknown',
            format_byte($jit['kind']),
            format_byte($jit['opt_level']),
            format_byte($jit['opt_flags']),
        ) : 'off';

        $preload     = $status['preload_statistics'] ?? null;
        $preloadInfo = null === $preload ? 'not active' : sprintf(
            'memory: %s, function: %d, class: %d, script: %d',
            format_byte($preload['memory_consumption']),
            is_countable($preload['functions']) ? \count($preload['functions']) : 0,
            is_countable($preload['classes']) ? \count($preload['classes']) : 0,
            is_countable($preload['scripts']) ? \count($preload['scripts']) : 0
        );

        return [
            ['label' => 'memory', 'value' => sprintf(
                '%s / %s, wasted %s (%.2f)',
                format_byte($memory['used_memory']),
                format_byte($memory['free_memory']),
                format_byte($memory['wasted_memory']),
                $memory['current_wasted_percentage'],
            )],
            ['label' => 'interned_strings', 'value' => sprintf(
                '%s / %s (%s)',
                format_byte($interned_strings['used_memory']),
                format_byte($interned_strings['free_memory']),
                format_byte($interned_strings['buffer_size']),
            )],
            ['label' => 'hits (misses)', 'value' => sprintf(
                '%d (%d), hit_rate: %.2f',
                $statistics['hits'],
                $statistics['misses'],
                $statistics['opcache_hit_rate'],
            )],
            ['label' => 'jit', 'value' => $jitInfo],
            ['label' => 'preload', 'value' => $preloadInfo],
        ];
    }

    public static function xdebugInfo(): string
    {
        if (!\extension_loaded('Xdebug')) {
            return 'Xdebug not active';
        }

        $version = phpversion('Xdebug');

        return sprintf('Xdebug: %s, mode: %s', $version ?: 'unknown', \ini_get('xdebug.mode') ?: 'null');
    }

    public static function mainExtension(): array
    {
        $array = [];

        foreach (
            [
                'cURL'      => Closure::fromCallable([self::class, '_curlInfo']),
                'mbstring'  => 'mbstring',
                'openssl'   => fn () => \defined('\OPENSSL_VERSION_TEXT') ? OPENSSL_VERSION_TEXT : 'not active',
                'bcmath'    => 'BCMath',
                'sodium'    => 'sodium',
                'fileinfo'  => 'fileinfo',
                'zlib'      => fn () => \defined('\ZLIB_VERSION') ? ZLIB_VERSION : 'not active',
                'redis'     => 'Redis',
                'xlswriter' => 'XlsWriter',
            ] as $name => $label
        ) {
            if (\is_callable($label)) {
                $array[] = ['label' => $name, 'value' => $label()];
            } else {
                $array[] = ['label' => $label, 'value' => phpversion($name) ?: 'not active'];
            }
        }

        return $array;
    }

    private static function _curlInfo(): string
    {
        if (!\extension_loaded('curl')) {
            $status = 'not active';
        } elseif (!\function_exists('\curl_version')) {
            $status = 'unknown';
        } else {
            $info = curl_version();
            /**
             * array (
             *  'version_number' => 476160,
             *  'age' => 5,
             *  'features' => 12568477,
             *  'ssl_version_number' => 0,
             *  'version' => '7.68.0',
             *  'host' => 'x86_64-pc-linux-gnu',
             *  'ssl_version' => 'OpenSSL/1.1.1f',
             *  'libz_version' => '1.2.11',
             *  'protocols' =>
             *      array (
             *          0 => 'dict',
             *          1 => 'file',
             *          2 => 'ftp',
             *          3 => 'ftps',
             *          4 => 'gopher',
             *          5 => 'http',
             *          6 => 'https',
             *          7 => 'imap',
             *          8 => 'imaps',
             *          9 => 'ldap',
             *          10 => 'ldaps',
             *          11 => 'pop3',
             *          12 => 'pop3s',
             *          13 => 'rtmp',
             *          14 => 'rtsp',
             *          15 => 'scp',
             *          16 => 'sftp',
             *          17 => 'smb',
             *          18 => 'smbs',
             *          19 => 'smtp',
             *          20 => 'smtps',
             *          21 => 'telnet',
             *          22 => 'tftp',
             *      ),
             *  'ares' => '',
             *  'ares_num' => 0,
             *  'libidn' => '2.3.0',
             *  'iconv_ver_num' => 0,
             *  'libssh_version' => 'libssh/0.9.3/openssl/zlib',
             *  'brotli_ver_num' => 16777223,
             *  'brotli_version' => '1.0.7',
             * )
             */

            $status = sprintf('%s, ssl (%s), libz (%s), brotli (%s)', $info['version'], $info['ssl_version'], $info['libz_version'], $info['brotli_version']);
        }

        return $status;
    }
}
