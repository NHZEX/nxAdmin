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

use app\Exception\AccessControl;
use app\Exception\ExceptionIgnoreRecord;
use app\Exception\ModelLogicException;
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
use Util\Reply;
use Zxin\Think\Auth\Record\RecordHelper;
use function array_diff_key;
use function get_class;
use function in_array;

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
        404, 200, 201, 202, 204, 301, 302, 303, 304, 307,
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
        if ($exception instanceof ModelException && $exception->getCode() === CODE_MODEL_OPTIMISTIC_LOCK) {
            return true;
        }
        if ($exception instanceof AccessControl) {
            return true;
        }
        if ($exception instanceof ModelLogicException) {
            return true;
        }
        return false;
    }

    public function render($request, Throwable $e): Response
    {
        // 捕获乐观锁错误
        if ($e instanceof ModelException && $e->getCode() === CODE_MODEL_OPTIMISTIC_LOCK) {
            RecordHelper::recordException($e);
            return Reply::bad($e->getCode(), $e->getMessage(), null, 403);
        }
        // 捕获访问控制异常
        if ($e instanceof AccessControl) {
            RecordHelper::recordException($e);
            return Reply::bad($e->getCode(), $e->getMessage(), null, 403);
        }
        // 模型业务逻辑错误
        if ($e instanceof ModelLogicException) {
            RecordHelper::recordException($e);
            return Reply::bad($e->getCode(), $e->getMessage());
        }
        // 渲染其他异常
        return parent::render($request, $e);
    }

    protected function convertExceptionToArray(Throwable $exception): array
    {
        if ($this->app->isDebug()) {
            // 调试模式，获取详细的错误信息
            $traces        = [];
            $nextException = $exception;
            do {
                $traces[] = [
                    'name'    => get_class($nextException),
                    'file'    => $nextException->getFile(),
                    'line'    => $nextException->getLine(),
                    'code'    => $this->getCode($nextException),
                    'message' => $this->getMessage($nextException),
                    'trace'   => $nextException->getTrace(),
                    'source'  => $this->getSourceCode($nextException),
                ];
            } while ($nextException = $nextException->getPrevious());
            $data = [
                'code'    => $this->getCode($exception),
                'message' => $this->getMessage($exception),
                'traces'  => $traces,
                'datas'   => $this->getExtendData($exception),
                'tables'  => [
                    'GET Data'            => $this->app->request->get(),
                    'POST Data'           => $this->app->request->post(),
                    'Files'               => $this->app->request->file(),
                    'Cookies'             => $this->app->request->cookie(),
                    'Session'             => $this->app->exists('session') ? $this->app->session->all() : [],
                    'Server/Request Data' => array_diff_key($this->app->request->server(), $_ENV),
                ],
            ];
        } else {
            // 部署模式仅显示 Code 和 Message
            $data = [
                'code'    => $this->getCode($exception),
                'message' => $this->getMessage($exception),
            ];

            if (!$this->app->config->get('app.show_error_msg')) {
                // 不显示详细错误信息
                $data['message'] = $this->app->config->get('app.error_message');
            }
        }

        return $data;
    }
}
