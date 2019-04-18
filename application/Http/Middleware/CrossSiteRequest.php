<?php
/**
 * Created by PhpStorm.
 * User: NHZEXG
 * Date: 2019/3/2
 * Time: 10:39
 */

namespace app\Http\Middleware;

use think\facade\App;
use think\facade\Env;
use think\Request;
use think\Response;

class CrossSiteRequest extends Middleware
{
    /** @var bool 关闭跨域处理器 */
    protected $close = true;

    /**
     * 跨域请求处理器
     * @param Request $request
     * @param \Closure $next
     * @return Response|string
     */
    public function handle(Request $request, \Closure $next)
    {
        // TODO[Low] 实现不正确，需要重新设计
        // 这里的域组成为：协议 + 域名 + 端口号，如果三者都相同则为同域，任意一个不同为跨域（同源策略）。
        // 处理 OPTIONS 请求

        if ($request->isOptions()) {
            $result = Response::create('415 Unsupported Media Type', '', 415);
        } elseif ($this->close) {
            $result = $next($request);
        } else {
            $referer_url = $request->header('referer', false);
            $referer = parse_url($referer_url) ?: [];
            $referer_host = ($referer['host'] ?? '').(isset($referer['port']) ? (':' . $referer['port']) : '');

            if (App::isDebug() && Env::get('develop.secure_domain_name', null) === ($referer['host'] ?? '')) {
                // 安全域不做验证
                $result = $next($request);
            } elseif ($referer_url && !$this->isSimpleRequest($request) && $referer_host !== $request->host()) {
                // 请求来源存在 且 请求非简单请求 且 来源域与请求域不一致，拒绝访问
                $result = Response::create('405 Not Allowed', '', 405);
            } else {
                $result = $next($request);
            }
        }

        return $result;
    }

    /**
     * 是否为简单请求
     * 非标准定义的简单请求
     * @param Request $request
     * @return bool
     */
    public function isSimpleRequest(Request $request)
    {
        return $request->isGet() || $request->isHead();
    }
}
