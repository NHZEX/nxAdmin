<?php

declare(strict_types=1);

namespace Util;

use app\ExceptionHandle;
use RuntimeException;
use think\App;
use think\Collection;
use think\Paginator;
use think\Response;
use think\response\Json;
use Throwable;
use function array_map;
use function substr;
use function Zxin\debug_array;
use function Zxin\set_path_cut_len;

class Reply
{
    /**
     * 响应请求资源创建成功
     */
    public static function create($data = '', array $header = []): Response
    {
        return self::success($data, 201, $header);
    }

    /**
     * 响应请求资源不存在.
     */
    public static function notFound(?int $code = null, ?string $msg = null, ?array $data = null): Response
    {
        return self::bad($code, $msg, $data, 404);
    }

    /**
     * 响应请求table资源.
     *
     * @param array|Collection|Paginator $data
     */
    public static function table($data = null, int $code = 200): Response
    {
        if ($data instanceof Collection) {
            $result = $data->toArray();
        } elseif ($data instanceof Paginator) {
            $result = [
                'data' => $data->getCollection()->toArray(),
                'count' => $data->total(),
            ];
        } else {
            $result = $data;
        }

        return self::success($result, $code);
    }

    /**
     * 响应请求异常.
     */
    public static function exception(Throwable $exception, ?string $msg = null, int $httpCode = 500): Response
    {
        $app = App::getInstance();
        $rootpath_len = \strlen($app->getRootPath());
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
                if (isset($trace['args'])) {
                    $trace['args'] = debug_array($trace['args']);
                }

                return $trace;
            }, $trace);
        } while ($next = $next->getPrevious());

        if ($app->isDebug()) {
            $data = [
                'err_code' => $exception->getCode(),
                'err_line' => $exception->getLine(),
                'err_file' => substr($exception->getFile(), $rootpath_len),
                'err_trace' => $traces,
            ];
        }

        return self::error(CODE_EXCEPTION, $msg ?? $exception->getMessage(), $data ?? null, $httpCode);
    }

    /**
     * 响应成功
     *
     * @param string|int|array|object $data
     */
    public static function success($data = '', int $code = 200, array $header = []): Response
    {
        if (200 > $code || $code > 299) {
            throw new RuntimeException('http code only 200 ~ 299');
        }
        if (\is_array($data) || \is_object($data)) {
            return self::json($data, $code, $header);
        }
        if ('' === $data) {
            $code = 204;
        } elseif (204 === $code) {
            $data = '';
        }

        return self::text((string) $data, $code, $header);
    }

    /**
     * 响应请求被拒绝.
     */
    public static function bad(
        ?int $code = null,
        ?string $msg = null,
        ?array $data = null,
        int $httpCode = 400
    ): Response {
        if (400 > $httpCode || $httpCode > 499) {
            throw new RuntimeException('http code only 400 ~ 499');
        }

        return self::message($code, $msg, $data, $httpCode);
    }

    /**
     * 响应请求发生错误.
     */
    public static function error(
        ?int $code = null,
        ?string $msg = null,
        ?array $data = null,
        int $httpCode = 500
    ): Response {
        if (500 > $httpCode || $httpCode > 599) {
            throw new RuntimeException('http code only 500 ~ 599');
        }

        return self::message($code, $msg, $data, $httpCode);
    }

    /**
     * 响应通用消息结构.
     */
    public static function message(?int $code, ?string $msg, ?array $data, int $httpCode): Response
    {
        $code ??= CODE_ERROR;
        $content = [
            'message' => $msg ?: self::strerror($code),
            'errno' => $code,
        ];
        if ($data) {
            $content += $data;
        }

        return self::json($content, $httpCode);
    }

    /**
     * 响应text内容.
     *
     * @param int $code
     */
    public static function text($data, $code = 200, array $header = []): Response
    {
        return Response::create($data, 'html', $code)
            ->contentType('text/plain')
            ->header($header);
    }

    /**
     * 响应html内容.
     *
     * @param int $code
     */
    public static function html(string $data, $code = 200, array $header = []): Response
    {
        return Response::create($data, 'html', $code)
            ->header($header);
    }

    /**
     * 响应json内容.
     *
     * @param array $data
     */
    public static function json($data = [], int $code = 200, array $header = [], array $options = []): Response
    {
        /** @var Json $json */
        $json = App::getInstance()->invokeClass(Json::class, [$data, $code]);
        $json->header($header)->options($options + [
            'json_encode_param' => \JSON_UNESCAPED_UNICODE | \JSON_UNESCAPED_SLASHES,
        ]);

        return $json;
    }

    /**
     * 将错误码转换为错误消息.
     */
    public static function strerror(int $code): string
    {
        return CODE_DICT[$code] ?? 'unknown';
    }
}
