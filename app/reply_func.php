<?php /** @noinspection PhpUnused */

namespace func\reply;

use app\ExceptionHandle;
use RuntimeException;
use think\App;
use think\Collection;
use think\Paginator;
use think\Response;
use think\response\Json;
use Throwable;
use Tp\Paginator2;
use function array_map;
use function HuangZx\debug_array;
use function HuangZx\set_path_cut_len;
use function is_array;
use function is_object;
use function strlen;
use function substr;

/**
 * 响应请求资源创建成功
 * @param string $data
 * @param array  $header
 * @return Response
 */
function reply_create($data = '', array $header = [])
{
    return reply_succeed($data, 201, $header);
}

/**
 * 响应请求资源不存在
 * @param int|null    $code
 * @param string|null $msg
 * @param array|null  $data
 * @return Response
 */
function reply_not_found(?int $code = null, ?string $msg = null, ?array $data = null)
{
    return reply_bad($code, $msg, $data, 404);
}

/**
 * 响应请求table资源
 * @param array|Collection|Paginator $data
 * @param int                        $code
 * @return Response
 */
function reply_table(iterable $data = null, int $code = 200): Response
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
    return reply_succeed($result, $code);
}

/**
 * 响应请求异常
 * @param Throwable $exception
 * @param string    $msg
 * @param int       $httpCode
 * @return Response
 */
function reply_exception(Throwable $exception, ?string $msg = null, int $httpCode = 500): Response
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
            if (isset($trace['args'])) {
                $trace['args'] = debug_array($trace['args']);
            }
            return $trace;
        }, $trace);
    } while ($next = $next->getPrevious());

    if ($app->isDebug()) {
        $data = [
            'err_code'  => $exception->getCode(),
            'err_line'  => $exception->getLine(),
            'err_file'  => substr($exception->getFile(), $rootpath_len),
            'err_trace' => $traces,
        ];
    }

    return reply_error(CODE_EXCEPTION, $msg ?? $exception->getMessage(), $data ?? null, $httpCode);
}

/**
 * 响应成功
 * @param       $data
 * @param int   $code
 * @param array $header
 * @return Response
 */
function reply_succeed($data = '', int $code = 200, array $header = [])
{
    if (200 > $code || $code > 299) {
        throw new RuntimeException('http code only 200 ~ 299');
    }
    if (is_array($data) || is_object($data)) {
        return reply_json($data, $code, $header);
    }
    if ($data === '') {
        $code = 204;
    } elseif ($code === 204) {
        $data = '';
    }
    return reply_text($data, $code, $header);
}

/**
 * 响应请求被拒绝
 * @param int|null    $code
 * @param string|null $msg
 * @param array|null  $data
 * @param int|null    $httpCode
 * @return Response
 */
function reply_bad(?int $code = null, ?string $msg = null, ?array $data = null, int $httpCode = 400)
{
    if (400 > $httpCode || $httpCode > 499) {
        throw new RuntimeException('http code only 400 ~ 499');
    }
    $code = $code ?? CODE_ERROE;
    $content = [
        'message' => $msg ?: (CODE_DICT[$code] ?? 'unknown'),
        'errno'   => $code,
    ];
    if ($data) {
        $content += $data;
    }
    return reply_json($content, $httpCode);
}

/**
 * 响应请求发生错误
 * @param int|null    $code
 * @param string|null $msg
 * @param array|null  $data
 * @param int|null    $httpCode
 * @return Response
 */
function reply_error(?int $code = null, ?string $msg = null, ?array $data = null, int $httpCode = 500)
{
    if (500 > $httpCode || $httpCode > 599) {
        throw new RuntimeException('http code only 500 ~ 599');
    }
    $code = $code ?? CODE_ERROE;
    $content = [
        'message' => $msg ?: (CODE_DICT[$code] ?? 'unknown'),
        'errno'   => $code,
    ];
    if ($data) {
        $content += $data;
    }
    return reply_json($content, $httpCode);
}

/**
 * 响应text内容
 * @param string $data
 * @param int    $code
 * @param array  $header
 * @return Response
 */
function reply_text($data, $code = 200, array $header = []): Response
{
    return Response::create($data, 'html', $code)
        ->contentType('text/plain')
        ->header($header);
}

/**
 * 响应html内容
 * @param string $data
 * @param int    $code
 * @param array  $header
 * @return Response
 */
function reply_html(string $data, $code = 200, array $header = []): Response
{
    return Response::create($data, 'html', $code)
        ->header($header);
}

/**
 * 响应json内容
 * @param array $data
 * @param int   $code
 * @param array $header
 * @param array $options
 * @return Response
 */
function reply_json($data = [], int $code = 200, array $header = [], array $options = []): Response
{
    /** @var Json $json */
    $json = App::getInstance()->invokeClass(Json::class, [$data, $code]);
    $json->header($header)->options($options + [
            'json_encode_param' => JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
        ]);
    return $json;
}
