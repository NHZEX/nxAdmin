<?php

namespace app\Model;

use think\App;
use Throwable;

/**
 * 系统异常日志
 * Class ExceptionLogs
 *
 * @package app\common\model
 * @property int $create_time
 * @property string $request_url
 * @property string $request_route
 * @property string $request_method
 * @property string $request_ip
 * @property string $mode
 * @property string $request_info
 * @property string $message
 * @property string $trace_info
 */
class ExceptionLogs extends Base
{
    protected $table = 'exception_logs';
    protected $pk = 'id';

    // 无需记录更新时间
    protected $updateTime = false;

    protected $type = [
        'request_info' => 'json'
    ];

    const TYPE_MIXED = 'mixed';
    const TYPE_HTTP = 'http';

    /**
     * 压入日志
     * @param Throwable $exception
     * @return false|int
     */
    public static function push(Throwable $exception)
    {
        $that = new self();

        $cli = is_cli() ? 'cli' : 'other';
        $sapi = PHP_SAPI;

        $request = App::getInstance()->request;
        $http = App::getInstance()->http;
        $route_info = "route:{$http->getName()}/{$request->controller()}/{$request->action()}";

        $that->request_ip = $request->ip();
        $that->request_url = "{$request->host()}{$request->baseUrl()}";
        $that->request_route = $route_info;
        $that->request_method = $request->method();
        $that->mode = "{$cli}/{$sapi}";
        $that->request_info = [
            'param' => $request->param(),
            'server' => $request->server(),
            'env' => $request->env(),
        ];
        $that->message = "[{$exception->getCode()}] {$exception->getMessage()}";

        $msg = '';
        $trace = $exception;
        do {
            $msg .= 'Class: ' . get_class($trace) . "\n";
            $msg .= "Stack Trace: [{$trace->getCode()}] {$trace->getMessage()}\n";
            $msg .= "{$trace->getTraceAsString()}\n";
        } while ($trace = $trace->getPrevious());

        $that->trace_info = $msg;

        return $that->save();
    }
}
