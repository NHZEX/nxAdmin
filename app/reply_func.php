<?php

/** @noinspection PhpUnused */

namespace func\reply;

use think\Collection;
use think\Paginator;
use think\Response;
use Throwable;
use Util\Reply;

/**
 * 响应请求资源创建成功
 * @deprecated
 * @param string $data
 * @param array  $header
 * @return Response
 */
function reply_create($data = '', array $header = [])
{
    return Reply::create($data, $header);
}

/**
 * 响应请求资源不存在
 * @deprecated
 * @param int|null    $code
 * @param string|null $msg
 * @param array|null  $data
 * @return Response
 */
function reply_not_found(?int $code = null, ?string $msg = null, ?array $data = null): Response
{
    return Reply::notFound($code, $msg, $data);
}

/**
 * 响应请求table资源
 * @deprecated
 * @param array|Collection|Paginator $data
 * @param int                        $code
 * @return Response
 */
function reply_table($data = null, int $code = 200): Response
{
    return Reply::table($data, $code);
}

/**
 * 响应请求异常
 * @deprecated
 * @param Throwable   $exception
 * @param string|null $msg
 * @param int         $httpCode
 * @return Response
 */
function reply_exception(Throwable $exception, ?string $msg = null, int $httpCode = 500): Response
{
    return Reply::exception($exception, $msg, $httpCode);
}

/**
 * 响应成功
 * @deprecated
 * @param string|int|array|object $data
 * @param int                     $code
 * @param array                   $header
 * @return Response
 */
function reply_succeed($data = '', int $code = 200, array $header = []): Response
{
    return Reply::success($data, $code, $header);
}

/**
 * 响应请求被拒绝
 * @deprecated
 * @param int|null    $code
 * @param string|null $msg
 * @param array|null  $data
 * @param int         $httpCode
 * @return Response
 */
function reply_bad(?int $code = null, ?string $msg = null, ?array $data = null, int $httpCode = 400): Response
{
    return Reply::bad($code, $msg, $data, $httpCode);
}

/**
 * 响应请求发生错误
 * @deprecated
 * @param int|null    $code
 * @param string|null $msg
 * @param array|null  $data
 * @param int         $httpCode
 * @return Response
 */
function reply_error(?int $code = null, ?string $msg = null, ?array $data = null, int $httpCode = 500): Response
{
    return Reply::error($code, $msg, $data, $httpCode);
}

/**
 * 响应通用消息结构
 * @deprecated
 * @param int|null    $code
 * @param string|null $msg
 * @param array|null  $data
 * @param int         $httpCode
 * @return Response
 */
function reply_message(?int $code, ?string $msg, ?array $data, int $httpCode): Response
{
    return Reply::message($code, $msg, $data, $httpCode);
}

/**
 * 响应text内容
 * @deprecated
 * @param string $data
 * @param int    $code
 * @param array  $header
 * @return Response
 */
function reply_text($data, $code = 200, array $header = []): Response
{
    return Reply::text($data, $code, $header);
}

/**
 * 响应html内容
 * @deprecated
 * @param string $data
 * @param int    $code
 * @param array  $header
 * @return Response
 */
function reply_html(string $data, $code = 200, array $header = []): Response
{
    return Reply::html($data, $code, $header);
}

/**
 * 响应json内容
 * @deprecated
 * @param array $data
 * @param int   $code
 * @param array $header
 * @param array $options
 * @return Response
 */
function reply_json($data = [], int $code = 200, array $header = [], array $options = []): Response
{
    return Reply::json($data, $code, $header, $options);
}

/**
 * 将错误码转换为错误消息
 * @deprecated
 * @param int $code
 * @return string
 */
function strerror(int $code): string
{
    return Reply::strerror($code);
}
