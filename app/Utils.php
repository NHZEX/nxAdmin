<?php
declare(strict_types=1);

namespace app;

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
}
