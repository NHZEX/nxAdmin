<?php

declare(strict_types=1);

namespace app\Service\DebugHelper;

use app\Service\Context;
use ArrayObject;
use Closure;
use stdClass;
use think\App;
use Zxin\Think\Redis\RedisManager;
use function Zxin\Util\format_byte;

class DebugHelper
{
    private static array $timeList = [];
    private static bool $memRealUsage = false;

    public static function startTime(string $name, ?bool $realMemoryUsage = null): void
    {
        $memRealUsage = $realMemoryUsage ?? self::$memRealUsage;

        $obj = new stdClass();
        $obj->time = microtime(true);
        $obj->memory = memory_get_usage($memRealUsage);
        $obj->peakMemory = memory_get_peak_usage($memRealUsage);
        $obj->memRealUsage = $memRealUsage;
        self::$timeList[$name] = $obj;
    }

    public static function endTime(string $name): string
    {
        $obj = self::$timeList[$name] ?? null;
        if (empty($obj)) {
            return "{$name} not ready";
        }
        unset(self::$timeList[$name]);
        $endTime = microtime(true);

        $endMemory = memory_get_usage($obj->memRealUsage);
        $endPeakMemory = memory_get_peak_usage($obj->memRealUsage);

        return \sprintf(
            '[%d]%s, time: %.3f, mem%s: %s(%s)',
            getmypid(),
            $name,
            $endTime - $obj->time,
            self::$memRealUsage ? '[R]' : '',
            format_byte($endMemory - $obj->memory, 3),
            format_byte($endPeakMemory - $obj->peakMemory, 3),
        );
    }

    public static function endTimeWithRecord(string $name, ?string $note = null): string
    {
        $obj = self::$timeList[$name] ?? null;
        if (empty($obj)) {
            return "{$name} not ready";
        }
        unset(self::$timeList[$name]);
        $endTime = microtime(true);

        $duration = \sprintf('%.3fs', $endTime - $obj->time);

        self::recordDuration($name, $duration, $note);

        $endMemory = memory_get_usage($obj->memRealUsage);
        $endPeakMemory = memory_get_peak_usage($obj->memRealUsage);

        $message = \sprintf(
            '[%d]%s => time: %s; mem%s: %s(%s)%s',
            getmypid(),
            $name,
            $duration,
            self::$memRealUsage ? '[R]' : '',
            format_byte($endMemory - $obj->memory, 3),
            format_byte($endPeakMemory - $obj->peakMemory, 3),
            $note ? ", {$note}" : '',
        );

        log_debug($message);

        return $message;
    }

    public static function fnExecuteTimeWithRecord(string $name, Closure $fn): mixed
    {
        self::startTime($name);
        try {
            return $fn();
        } finally {
            self::endTimeWithRecord($name);
        }
    }

    public static function recordDuration(string $name, string $message, ?string $note = null): void
    {
        if (!Context::hasData('__DEBUG_TIME_LOGS')) {
            $ctx = new ArrayObject(flags: ArrayObject::ARRAY_AS_PROPS);
            Context::setData('__DEBUG_TIME_LOGS', $ctx);
        } else {
            $ctx = Context::getData('__DEBUG_TIME_LOGS');
        }

        $ctx->{$name} = $message.($note ? ", {$note}" : '');
    }

    public static function getTimerRecord(): string
    {
        /** @var ArrayObject|null $debugTimes */
        $debugTimes = Context::getData('__DEBUG_TIME_LOGS');

        if (null === $debugTimes) {
            return '';
        }

        $strArr = [];
        foreach ($debugTimes as $key => $message) {
            $strArr[] = "{$key}=\"{$message}\"";
        }

        return implode('; ', $strArr);
    }

    public static function refreshRequestFrequency(?string $actionName, int $hour = 24): void
    {
        if (null === $actionName) {
            $request = App::getInstance()->request;

            $actionName = str_replace('/', '_', trim($request->baseUrl(), '/'));
        }

        $target = "request_rate:{$actionName}";

        if (Context::hasData($target)) {
            return;
        }

        $time = date('ymdHi');
        $key = "{$target}:{$time}";
        Context::setData($target, $time);

        $redis = RedisManager::connection();
        $val = $redis->incr($key);
        if (1 === $val) {
            $redis->expire($key, 3600 * $hour);
        }
    }
}
