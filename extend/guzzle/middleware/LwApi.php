<?php
/**
 * Created by PhpStorm.
 * User: NHZEXG
 * Date: 2018/12/24
 * Time: 12:01
 */

namespace guzzle\middleware;


use guzzle\psr7\JsonAwareResponse;
use GuzzleHttp\Promise\Promise;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class LwApi
{
    public static function CustomProcessing()
    {
        return function (callable $handler) {
            return function (RequestInterface $request, array $options) use ($handler) {
                // 写入真实的请求域
                $request = $request->withHeader('Host', $options['real_host']);
                /** @var Promise $promise */
                $promise = $handler($request, $options);

                // 处理响应
                return $promise->then(function (ResponseInterface $response) use ($options) {
                    // 自动Json解码
                    if($options['decode_json']) {
                        $newResponse =  new JsonAwareResponse(
                            $response->getStatusCode(),
                            $response->getHeaders(),
                            $response->getBody(),
                            $response->getProtocolVersion(),
                            $response->getReasonPhrase()
                        );
                    } else {
                        $newResponse = $response;
                    }
                    return $newResponse;
                });
            };
        };
    }
}
