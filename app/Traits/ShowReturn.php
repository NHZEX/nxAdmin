<?php
/**
 * Created by PhpStorm.
 * User: NHZEXG
 * Date: 2018/4/19
 * Time: 14:47
 */

namespace app\Traits;

use app\ExceptionHandle;
use think\App;
use think\Collection;
use think\Paginator;
use think\Response;
use think\response\Redirect;
use Throwable;
use Tp\Paginator2;
use function HuangZx\debug_array;
use function HuangZx\set_path_cut_len;

trait ShowReturn
{
    /**
     * 统一返回 被禁止的访问 未经授权(Forbidden)
     * see RFC7231 and RFC7235.
     * @param string $code
     * @param string $msg
     * @param array  $header
     * @return Response
     * @author NHZEXG
     */
    protected static function show403($code = '', $msg = '', array $header = []): Response
    {
        return self::showReturn($code, null, $msg, false, $header, 403);
    }

    /**
     * 统一返回 被拒绝的访问 未经认证(Unauthorized)
     * see RFC7231 and RFC7235.
     * @param string $code
     * @param string $msg
     * @param array  $header
     * @return Response
     * @author NHZEXG
     */
    protected static function show401($code = '', $msg = '', array $header = []): Response
    {
        return self::showReturn($code, null, $msg, false, $header, 401);
    }

    /**
     * 统一返回 跳转
     * @param string $url
     * @param array  $header
     * @return Response
     */
    protected static function show302(string $url, array $header = []): Response
    {
        /** @var Redirect $redirect */
        $redirect = Response::create($url, 'redirect', 302);
        return $redirect->header($header);
    }

    /**
     * 统一返回 HTTP状态
     * @param string|int $code
     * @param string     $msg
     * @param array      $header
     * @return Response
     */
    protected static function showHttpCode($code, string $msg = '', array $header = []): Response
    {
        return Response::create($msg, 'html', $code)
            ->header($header);
    }

    /**
     * 统一返回 普通
     * @param string|int $code
     * @param mixed      $data
     * @param string     $msg
     * @param array      $header
     * @return Response
     */
    protected static function showData($code = '', $data = null, string $msg = '', array $header = []): Response
    {
        return self::showReturn($code, $data, $msg, false, $header);
    }

    /**
     * 统一返回 只返回消息
     * @param string|int $code
     * @param string     $msg
     * @param array      $header
     * @param int        $http_code
     * @return Response
     * @author NHZEXG
     */
    protected static function showMsg($code = '', string $msg = '', array $header = [], $http_code = 200): Response
    {
        return self::showReturn($code, null, $msg, false, $header, $http_code);
    }

    /**
     * 统一返回 成功的消息
     * @param null   $data
     * @param string $msg
     * @param array  $header
     * @return Response
     */
    protected static function showSucceed($data = null, string $msg = '', array $header = []): Response
    {
        return self::showReturn(CODE_SUCCEED, $data, $msg, false, $header);
    }

    /**
     * 统一返回 返回简单异常
     * @param Throwable $exception
     * @param string    $msg
     * @param array     $header
     * @return Response
     * @author NHZEXG
     */
    protected static function showException(Throwable $exception, ?string $msg = null, $header = []): Response
    {
        $app = App::getInstance();
        $rootpath_len = strlen($app->getRootPath());
        set_path_cut_len($rootpath_len);
        /** @var ExceptionHandle $handle */
        $handle = $app->make(ExceptionHandle::class);
        $handle->report($exception);

        $traces = [];
        $next = $exception;
        do {
            $trace = $exception->getTrace();
            $traces[] = array_map(function ($trace) use ($rootpath_len) {
                if (isset($trace['file'])) {
                    $trace['file'] = substr($trace['file'], $rootpath_len);
                }
                if (isset($trace['args']) && function_exists('debug_array_ex')) {
                    $trace['args'] = debug_array($trace['args']);
                }
                return $trace;
            }, $trace);
        } while ($next = $next->getPrevious());

        $data = [
            'code' => CODE_ERROE,
            'msg' => $msg ?? $exception->getMessage(),
        ] + ($app->isDebug() ? [
            'err_code' => $exception->getCode(),
            'err_line' => $exception->getLine(),
            'err_file' => substr($exception->getFile(), $rootpath_len),
            'err_trace' => $traces,
        ] : []);
        return self::showReturn(null, $data, null, true, $header);
    }

    /**
     * 统一返回 可拓展格式 (表格用)
     * @param array|Collection|Paginator|Paginator2 $data
     * @param int                                   $code
     * @param string                                $msg
     * @return Response
     */
    protected static function showTable(iterable $data = null, int $code = CODE_SUCCEED, string $msg = ''): Response
    {
        if ($data instanceof Collection) {
            $result = $data->toArray();
        } elseif ($data instanceof Paginator || $data instanceof Paginator2) {
            $result = [
                'data' => $data->getCollection()->toArray(),
                'count' => $data->total(),
            ];
        } else {
            $result = $data;
        }
        return self::showReturn($code, $result, $msg, true);
    }

    /**
     * 统一返回 可拓展格式
     * @param string $code
     * @param array  $data
     * @param string $msg
     * @return Response
     */
    protected static function showExpand($code = '', $data = null, $msg = ''): Response
    {
        return self::showReturn($code, $data, $msg, true);
    }

    /**
     * 构造返回结果
     * @param string|int   $code 状态码 务必提交
     * @param array|string $data 返回数据 返回的数据
     * @param string       $msg  状态消息
     * @param bool         $merge
     * @param array        $header
     * @param string|int   $http_code
     * @param array        $options
     * @return Response
     */
    private static function showReturn(
        $code = null,
        $data = null,
        ?string $msg = '',
        bool $merge = false,
        array $header = [],
        $http_code = 200,
        array $options = []
    ): Response {
        $defult = [
            'code' => $code ?? -1,
            'msg' => 'undefined',
        ];

        if ($merge && is_array($data)) {
            foreach ($data as $key => $value) {
                $defult[$key] = $value;
            }
        } else {
            $defult['data'] = $data;
        }
        //空消息处理
        if (!empty($msg)) {
            $defult['msg'] = $msg;
        } elseif (CODE_DICT[$code] ?? false) {
            $defult['msg'] = CODE_DICT[$code];
        }
        return Response::create($defult, 'json', $http_code)
            ->header($header)
            ->options($options);
    }
}
