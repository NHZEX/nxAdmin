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
use think\response\Json;
use Throwable;
use Tp\Paginator2;
use function HuangZx\debug_array;
use function HuangZx\set_path_cut_len;

trait ShowReturn2
{
    /**
     * 统一返回 HTTP状态
     * @param string|int $code
     * @param string     $data
     * @param array      $header
     * @return Response
     */
    protected static function showText($code, string $data = '', array $header = []): Response
    {
        return Response::create($data, 'html', $code)
            ->header($header);
    }

    /**
     * 统一返回 只返回消息
     * @param int    $code
     * @param string $msg
     * @param array  $data
     * @param array  $header
     * @return Response
     */
    protected static function showCode(int $code, string $msg = '', array $data = [], array $header = []): Response
    {
        return self::showJson([
            'message' => $msg ?: (STATUS_TEXTS[$code] ?? 'Unknown'),
        ] + $data, $code, $header);
    }

    /**
     * 统一返回 只返回消息
     * @param string     $msg
     * @param string|int $code
     * @param array      $data
     * @param array      $header
     * @return Response
     */
    protected static function showMsg(string $msg = '', int $code = 200, array $data = [], array $header = []): Response
    {
        return self::showJson([
                'message' => $msg ?: (STATUS_TEXTS[$code] ?? 'Unknown'),
            ] + $data, $code, $header);
    }

    /**
     * 统一返回 成功的消息
     * @param array|null $data
     * @param string     $msg
     * @param array      $header
     * @return Response
     */
    protected static function showSucceed(array $data = null, string $msg = 'success', array $header = []): Response
    {
        return self::showMsg($msg, 200, $data ?? [], $header);
    }

    /**
     * 统一返回 返回简单异常
     * @param Throwable $exception
     * @param string    $msg
     * @param array     $header
     * @return Response
     */
    protected static function showException(Throwable $exception, ?string $msg = null, $header = []): Response
    {
        $app          = App::getInstance();
        $rootpath_len = strlen($app->getRootPath());
        set_path_cut_len($rootpath_len);
        /** @var ExceptionHandle $handle */
        $handle = $app->make(ExceptionHandle::class);
        $handle->report($exception);

        $traces = [];
        $next   = $exception;
        do {
            $trace    = $exception->getTrace();
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
                'msg'  => $msg ?? $exception->getMessage(),
            ] + ($app->isDebug() ? [
                'err_code'  => $exception->getCode(),
                'err_line'  => $exception->getLine(),
                'err_file'  => substr($exception->getFile(), $rootpath_len),
                'err_trace' => $traces,
            ] : []);

        return self::showJson($data, 500, $header);
    }

    /**
     * 统一返回 (表格用)
     * @param array|Collection|Paginator $data
     * @param int                        $code
     * @return Response
     */
    protected static function showTable(iterable $data = null, int $code = 200): Response
    {
        if ($data instanceof Collection) {
            $result = $data->toArray();
        } elseif ($data instanceof Paginator || $data instanceof Paginator2) {
            $result = [
                'data'  => $data->getCollection()->toArray(),
                'count' => $data->total(),
            ];
        } else {
            $result = $data;
        }
        return self::showJson($result, $code);
    }

    /**
     * @param array $data
     * @param int   $code
     * @param array $header
     * @param array $options
     * @return Response
     */
    protected static function showJson($data = [], int $code = 200, array $header = [], array $options = []): Response
    {
        /** @var Json $json */
        $json = App::getInstance()->invokeClass(Json::class, [$data, $code]);
        $json->header($header)->options($options + [
                'json_encode_param' => JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
            ]);
        return $json;
    }
}
