<?php

namespace app\Model;

use app\Service\Auth\AuthHelper;
use think\App;
use Throwable;
use function json_encode_ex;
use function substr;
use function time;

/**
 * @property int    $id
 * @property int    $create_time    创建时间
 * @property string $request_url    请求地址
 * @property string $request_route  请求路由
 * @property string $request_method 请求方法
 * @property string $request_ip     请求IP
 * @property string $mode           类型
 * @property string $request_info   请求信息
 * @property string $message        消息
 * @property string $trace_info     异常堆栈
 */
class ExceptionLogs extends Base
{
    protected $table = 'exception_logs';
    protected $pk = 'id';

    // 无需记录更新时间
    protected $updateTime = false;

    protected $type = [
        'request_info' => 'json',
    ];

    public const TYPE_MIXED = 'mixed';
    public const TYPE_HTTP = 'http';

    /**
     * 写入日志
     * @param Throwable $exception
     * @return bool
     */
    public static function push(Throwable $exception): bool
    {
        $cli = is_cli() ? 'cli' : 'other';
        $sapi = PHP_SAPI;

        $request = App::getInstance()->request;
        $http = App::getInstance()->http;
        $route_info = "route:{$http->getName()}/{$request->controller()}/{$request->action()}";

        $requestInfo = json_encode_ex([
            'param' => $request->param(),
            'userId' => AuthHelper::id(),
        ]);
        if (\strlen($requestInfo) > 65535) {
            $requestInfo = substr($requestInfo, 0, 65535 - 16) . '<cut...>';
        }

        $msg = '';
        $trace = $exception;
        do {
            $msg .= 'Class: ' . $trace::class . "\n";
            $msg .= "Stack Trace: [{$trace->getCode()}] {$trace->getMessage()}\n";
            $msg .= "{$trace->getTraceAsString()}\n";
        } while ($trace = $trace->getPrevious());

        $traceInfo = substr($msg, 0, 65535);

        (new self())->insert([
            'create_time' => time(),
            'request_url' => "{$request->host()}{$request->baseUrl()}",
            'request_route' => $route_info,
            'request_method' => $request->method(),
            'mode' => "{$cli}/{$sapi}",
            'request_info' => $requestInfo,
            'message' => "[{$exception->getCode()}] {$exception->getMessage()}",
            'trace_info' => $traceInfo,
        ]);
        return true;
    }
}
