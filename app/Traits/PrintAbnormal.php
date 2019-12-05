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
     * @author NHZEXG
     */
    protected static function printAbnormalToLog(Throwable $e, ?string $type = null) :string
    {
        // 打印额外的POD异常信息
        if (app()->isDebug() && $e instanceof PDOException) {
            $db_info = $e->getData();
            if (isset($db_info['Database Config'])) {
                Log::record($db_info['Database Config'], 'db-config');
                unset($db_info['Database Config']);
            }
            Log::record($db_info, 'critical');
        }
        // 打印通用异常信息
        $msg = '';
        $trace = $e;
        do {
            $msg .= 'ExceptionClass: \\' . get_class($trace) . "\n";
            $msg .= "{$trace->getFile()}:{$trace->getLine()}\n";
            $msg .= "StackTrace: [{$trace->getCode()}] {$trace->getMessage()}\n";
            $msg .= "{$trace->getTraceAsString()}\n";
        } while ($trace = $trace->getPrevious());
        $msg .= '----------';
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
