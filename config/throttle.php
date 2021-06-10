<?php
// +----------------------------------------------------------------------
// | 节流设置
// +----------------------------------------------------------------------
use think\middleware\Throttle;
use think\Request;
use Util\Reply;

return [
    // 缓存键前缀，防止键值与其他应用冲突
    'prefix' => 'throttle:',
    // 缓存的键，true 表示使用来源ip
    'key' => true,
    // 要被限制的请求类型, eg: GET POST PUT DELETE HEAD 等
    'visit_method' => ['GET', 'HEAD'],
    // 设置访问频率，例如 '10/m' 指的是允许每分钟请求10次。值 null 表示不限制， eg: null 10/m  20/h  300/d 200/300
    'visit_rate' => '45/m',
    // 访问受限时返回的http状态码
    'visit_fail_code' => 429,
    // 访问受限时访问的文本信息
    'visit_fail_text' => '访问频率受到限制，请稍等__WAIT__秒再试',
    // 访问受限时的响应信息闭包回调（优先级高于：visit_fail_text）
    'visit_fail_response' => function (Throttle $t, Request $request, int $waitSeconds) {
        return Reply::bad(1, "{$request->url()}: 访问频率受到限制，请稍等{$waitSeconds}秒再试", null, 429);
    },
];
