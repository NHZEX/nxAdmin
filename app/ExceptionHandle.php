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

use app\Exception\ExceptionIgnoreRecord;
use app\Model\ExceptionLogs;
use app\Traits\PrintAbnormal;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\Handle;
use think\exception\HttpException;
use think\exception\HttpResponseException;
use think\exception\ValidateException;
use think\Response;
use Throwable;
use Tp\Model\Exception\ModelException;
use function func\reply\reply_bad;

/**
 * 应用异常处理类
 */
class ExceptionHandle extends Handle
{
    use PrintAbnormal;

    /**
     * 不需要记录信息（日志）的异常类列表
     * @var array
     */
    protected $ignoreReport = [
        HttpException::class,
        HttpResponseException::class,
        ModelNotFoundException::class,
        DataNotFoundException::class,
        ValidateException::class,
    ];

    protected $ignoreHttpCode = [
        404, 200, 201, 202, 204, 301, 302, 303, 304, 307
    ];

    /**
     * 记录异常信息（包括日志或者其它方式记录）
     *
     * @access public
     * @param  Throwable $exception
     * @return void
     */
    public function report(Throwable $exception): void
    {
        // 不对Http进行扩展记录
        // 不对降级异常进行扩展记录
        if (!$this->ignoreHttpException($exception)) {
            if (false === $exception instanceof ExceptionIgnoreRecord) {
                try {
                    ExceptionLogs::push($exception);
                    self::printException($exception);
                } catch (Throwable $throwable) {
                    $newException = new ExceptionIgnoreRecord('异常日志记录发生错误', 0, $throwable);
                    // 打印记录异常
                    self::printException($newException);
                }
            } else {
                // 打印异常日志
                self::printException($exception);
            }
        }
        // 交由系统处理
        parent::report($exception);
    }

    /**
     * @param Throwable $exception
     * @return bool
     */
    protected function ignoreHttpException(Throwable $exception)
    {
        if ($exception instanceof HttpException && in_array($exception->getStatusCode(), $this->ignoreHttpCode)) {
            return true;
        }
        if ($exception instanceof HttpResponseException) {
            return true;
        }
        return false;
    }

    public function render($request, Throwable $e): Response
    {
        // 捕获乐观锁错误
        if ($e instanceof ModelException && $e->getCode() === CODE_MODEL_OPTIMISTIC_LOCK) {
            return reply_bad($e->getCode(), $e->getMessage());
        }
        // 渲染其他异常
        return parent::render($request, $e);
    }
}
