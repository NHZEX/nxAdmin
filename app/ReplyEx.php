<?php

declare(strict_types=1);

namespace app;

use RuntimeException;
use think\App;
use think\Collection;
use think\Paginator;
use think\Response;
use think\response\Json;

class ReplyEx
{
    /**
     * 响应请求资源创建成功
     */
    public static function create($data = null, int $code = 0, ?string $message = null): Response
    {
        return self::success(data: $data, code: $code, message: $message, httpCode: 201);
    }

    /**
     * 响应请求资源不存在
     */
    public static function notFound($data = null, int $code = CODE_NOT_FOUND, ?string $message = null): Response
    {
        return self::bad(data: $data, code: $code, message: $message, httpCode: 404);
    }

    /**
     * 响应请求table资源
     * @param array|Collection|Paginator $data
     */
    public static function table($data = null, int $code = 0, array $extra = [], bool $merge = true): Response
    {
        if ($data instanceof Collection) {
            $result = $data->toArray();
        } elseif ($data instanceof Paginator) {
            $result = [
                'data'  => $data->getCollection()->toArray(),
                'total' => $data->total(),
                'count' => $data->total(),
                'page' => [
                    'size' => $data->listRows(),
                    'current' => $data->currentPage(),
                    'hasMore' => $data->hasPages(),
                ]
            ];
        } else {
            $result = $data;
        }
        return self::success(data: $result + $extra, code: $code, merge: $merge);
    }

    /**
     * 响应成功
     */
    public static function success(
        $data = null,
        int $code = 0,
        ?string $message = null,
        int $httpCode = 200,
        bool $merge = false,
    ): Response
    {
        if (200 > $httpCode || $httpCode > 299) {
            throw new RuntimeException('http code only 200 ~ 299');
        }
        if ('' === $data || null === $data) {
            $httpCode = 204;
        }
        if ($httpCode === 204) {
            $data = null;
        }
        return self::message(data: $data, code: $code, message: $message, httpCode: $httpCode, merge: $merge);
    }

    /**
     * 响应请求被拒绝
     */
    public static function bad(
        $data = null,
        ?int    $code = null,
        ?string $message = null,
        int     $httpCode = 400
    ): Response {
        if (400 > $httpCode || $httpCode > 499) {
            throw new RuntimeException('http code only 400 ~ 499');
        }

        return self::message(data: $data, code: $code, message: $message, httpCode: $httpCode);
    }

    /**
     * 响应请求发生错误
     */
    public static function error(
        $data = null,
        ?int    $code = null,
        ?string $message = null,
        int     $httpCode = 500
    ): Response {
        if (500 > $httpCode || $httpCode > 599) {
            throw new RuntimeException('http code only 500 ~ 599');
        }

        return self::message(data: $data, code: $code, message: $message, httpCode: $httpCode);
    }

    /**
     * 响应通用消息结构
     */
    public static function message(
        $data,
        ?int $code = 0,
        ?string $message = null,
        int $httpCode = 200,
        bool $merge = false
    ): Response
    {
        $code ??= CODE_ERROR;
        $content = [
            'message' => $message ?: self::strError($code),
            'code'    => $code,
        ];
        if ($merge) {
            if (!is_array($data)) {
                throw new RuntimeException('merge data must be array');
            }
            $content += $data;
        } else {
            $content['data'] = $data;
        }
        return self::json($content, $httpCode);
    }

    /**
     * 响应text内容
     */
    public static function text(string $data, int $httpCode = 200, array $header = []): Response
    {
        return Response::create($data, 'html', $httpCode)
            ->contentType('text/plain')
            ->header($header);
    }

    /**
     * 响应html内容
     */
    public static function html(string $data, int $code = 200, array $header = []): Response
    {
        return Response::create($data, 'html', $code)
            ->header($header);
    }

    /**
     * 响应json内容
     */
    public static function json(array $data, int $httpCode = 200, array $header = [], array $options = []): Response
    {
        /** @var Json $json */
        $json = App::getInstance()->invokeClass(Json::class, [$data, $httpCode]);
        $json->header($header)->options($options + [
                'json_encode_param' => JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
            ]);
        return $json;
    }

    /**
     * 将错误码转换为错误消息
     */
    public static function strError(int $code): string
    {
        return (CODE_DICT[$code] ?? 'unknown');
    }
}
