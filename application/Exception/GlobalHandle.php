<?php
/**
 * Created by PhpStorm.
 * User: NHZEXG
 * Date: 2017/8/30
 * Time: 10:46
 */

namespace app\Exception;

use app\common\Traits\PrintAbnormal;
use app\Model\ExceptionLogs;
use Exception;
use think\console\Output;
use think\exception\Handle;
use think\exception\HttpException;
use think\Response;
use Throwable;

/**
 * 全局异常处理器
 * Class GlobalHandle
 * @package app\common\exception
 */
class GlobalHandle extends Handle
{
    use PrintAbnormal;

    /**
     * 全局异常记录
     * @param Exception $exception
     */
    public function report(Exception $exception)
    {
        // 不对[http-404]进行高级记录
        // 不对降级异常进行高级记录
        if (false === $exception instanceof ExceptionRecordDown
            && !($exception instanceof HttpException && $exception->getStatusCode() === 404)
        ) {
            try {
                ExceptionLogs::push($exception);
            } catch (Throwable $throwable) {
                $newException = new ExceptionRecordDown('异常日志降级', 0, $throwable);
                // 打印记录异常
                self::printAbnormalToLog($newException);
            }
        }
        // 打印异常日志
        self::printAbnormalToLog($exception);
        // 交由系统处理
        parent::report($exception);
    }

    /**
     * Console异常处理
     * @param Output    $output
     * @param Exception $e
     * @author NHZEXG
     */
    public function renderForConsole(Output $output, Exception $e)
    {
        //交由系统处理
        parent::renderForConsole($output, $e);
    }

    /**
     * WebServer异常处理
     * @param Exception $e
     * @return Response
     *@author NHZEXG
     */
    public function render(Exception $e)
    {
        // 交回系统处理
        return parent::render($e);
    }

    /**
     * 将异常渲染为Web页码
     * @param Exception $exception
     * @return Response
     */
    public function convertExceptionToResponse(Exception $exception)
    {
        return parent::convertExceptionToResponse($exception);
    }
}
