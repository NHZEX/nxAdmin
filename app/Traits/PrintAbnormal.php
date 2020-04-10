<?php
/**
 * Created by PhpStorm.
 * User: NHZEXG
 * Date: 2018/5/5
 * Time: 14:54
 */

namespace app\Traits;

use think\db\exception\PDOException;
use think\facade\Log;
use Throwable;

trait PrintAbnormal
{
    /**
     * @param Throwable $e
     * @param null|string $type
     * @return string
     */
    protected static function printException(Throwable $e, ?string $type = null) :string
    {
        // 打印额外的POD异常信息
        if (app()->isDebug() && $e instanceof PDOException) {
            $sqlInfo = $e->getData();
            unset($sqlInfo['Database Config']);
            $errinfo = json_encode($sqlInfo, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
            Log::record("SQL ERROR: {$errinfo}", 'critical');
        }
        // 打印通用异常信息
        $msg = '';
        $trace = $e;
        do {
            $msg .= 'Class: \\' . get_class($trace) . "\n";
            $msg .= "Error: [{$trace->getCode()}] {$trace->getMessage()}\n";
            $msg .= "File : {$trace->getFile()}:{$trace->getLine()}\n";
            $msg .= "{$trace->getTraceAsString()}\n";
        } while ($trace = $trace->getPrevious());
        $msg .= '----END----';
        Log::record($msg, $type ?? 'critical');

        return $msg;
    }

    protected static function formatAbnormalToStr(Throwable $e) :string
    {
        $trace = $e;
        $msg = [];
        do {
            $msg[] = "[#{$trace->getCode()}]({$trace->getMessage()})";
        } while ($trace = $trace->getPrevious());
        return join(' > ', $msg);
    }

    protected static function showIncludedFiles() :array
    {
        $list = get_included_files();
        Log::record($list, 'debug');
        return $list;
    }
}
