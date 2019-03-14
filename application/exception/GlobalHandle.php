<?php
/**
 * Created by PhpStorm.
 * User: NHZEXG
 * Date: 2017/8/30
 * Time: 10:46
 */

namespace app\exception;

use app\common\traits\PrintAbnormal;
use app\model\ExceptionLogs;
use Exception;
use think\console\Output;
use think\exception\Handle;
use think\exception\HttpException;

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
        self::printAbnormalToLog($exception);

        // 对不是Http-404的错误进行日志记录
        if (!($exception instanceof HttpException && $exception->getStatusCode() === 404)) {
            ExceptionLogs::push($exception);
        }
        // 交由系统处理
        parent::report($exception);
    }

    /**
     * Console异常处理
     * @param Output $output
     * @param \Exception $e
     * @author NHZEXG
     */
    public function renderForConsole(Output $output, \Exception $e)
    {
        //交由系统处理
        parent::renderForConsole($output, $e);
    }

    /**
     * WebServer异常处理
     * @param \Exception $e
     * @author NHZEXG
     * @return \think\Response
     */
    public function render(\Exception $e)
    {
        // 交回系统处理
        return parent::render($e);
    }

    /**
     * 将异常渲染为Web页码
     * @param Exception $exception
     * @return \think\Response
     */
    public function convertExceptionToResponse(Exception $exception)
    {
        return parent::convertExceptionToResponse($exception);
    }
}
