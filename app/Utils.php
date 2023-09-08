<?php
declare(strict_types=1);

namespace app;

use Composer\InstalledVersions;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;
use think\console\Output;
use Zxin\Think\Symfony\Console\OutputBridge;

class Utils
{
    public static function closeLogChannel(string $channel): void
    {
        \app()->log->close($channel);
    }

    public static function closeLogRemoteChannel(): void
    {
        self::closeLogChannel('remote');
    }

    /**
     * @var \WeakMap<Output, LoggerInterface>|null
     */
    private static ?\WeakMap $loggerCache = null;

    public static function makeConsoleLogger(Output $output): LoggerInterface
    {
        if (self::$loggerCache === null) {
            self::$loggerCache = new \WeakMap();
        }

        if (isset(self::$loggerCache[$output])) {
            return self::$loggerCache[$output];
        }

        $bridge = new OutputBridge($output);
        $logger = new ConsoleLogger($bridge, [
            LogLevel::EMERGENCY => OutputInterface::VERBOSITY_NORMAL,
            LogLevel::ALERT     => OutputInterface::VERBOSITY_NORMAL,
            LogLevel::CRITICAL  => OutputInterface::VERBOSITY_NORMAL,
            LogLevel::ERROR     => OutputInterface::VERBOSITY_NORMAL,
            LogLevel::WARNING   => OutputInterface::VERBOSITY_NORMAL,
            LogLevel::NOTICE    => OutputInterface::VERBOSITY_NORMAL,
            LogLevel::INFO      => OutputInterface::VERBOSITY_NORMAL,
            LogLevel::DEBUG     => OutputInterface::VERBOSITY_VERY_VERBOSE,
        ]);

        self::$loggerCache[$output] = $logger;

        return $logger;
    }

    public static function getEnvInfo(): array
    {
        return [
            'sys_version' => ['服务器系统', \php_uname()],
            'server_software' => ['执行环境', $_SERVER['SERVER_SOFTWARE']],
            'php_sapi' => ['PHP接口类型', PHP_SAPI],
            'tp_version' => ['ThinkPHP 版本', InstalledVersions::getPrettyVersion("topthink/framework")],
            'orm_version' => ['ThinkORM 版本', InstalledVersions::getPrettyVersion("topthink/think-orm")],
            'php_version' => ['PHP版本', PHP_VERSION],
            'db_version' => ['数据库版本', \db_version(null, true)],
            'memory_limit' => ['内存限制', \ini_get('memory_limit')],
            'max_execution_time' => ['最长执行时间', \ini_get('max_execution_time')],
            'upload_max_filesize' => ['上传限制', \ini_get('upload_max_filesize')],
            'post_max_size' => ['POST限制', \ini_get('post_max_size')],
            'realpath_cache_size' => ['路径缓存', \realpath_cache_size()],
            'preload_statistics' => ['预加载', \preload_statistics()],
        ];
    }
}
