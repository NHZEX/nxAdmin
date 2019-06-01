<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2019 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
namespace app;
use app\common\Traits\PrintAbnormal;
use app\Exception\ExceptionRecordDown;
use app\Model\ExceptionLogs;
use think\exception\Handle;
use think\exception\HttpException;
use think\Response;
use Throwable;
/**
 * 应用异常处理类
 */
class ExceptionHandle extends Handle
{
    use PrintAbnormal;

    /**
     * 记录异常信息（包括日志或者其它方式记录）
     *
     * @access public
     * @param  Throwable $exception
     * @return void
     */
    public function report(Throwable $exception): void
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
     * Render an exception into an HTTP response.
     *
     * @access public
     * @param \think\Request   $request
     * @param Throwable $e
     * @return Response
     */
    public function render($request, Throwable $e): Response
    {
        // 添加自定义异常处理机制
        // 其他错误交给系统处理
        return parent::render($request, $e);
    }
}