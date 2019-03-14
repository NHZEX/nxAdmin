<?php
/**
 * Created by PhpStorm.
 * User: NHZEXG
 * Date: 2019/3/2
 * Time: 10:39
 */

namespace app\http\middleware;

use think\Request;
use think\Response;

class CrossSiteRequest extends Middleware
{

    /**
     * @param Request $request
     * @param \Closure $next
     * @return Response|string
     */
    public function handle(Request $request, \Closure $next)
    {
        $referer_url = $request->header('referer', false);
        $referer = parse_url($referer_url);
        $referer_host = '';
        if (is_array($referer)) {
            $referer_host = ($referer['host'] ?? '').(isset($referer['port']) ? (':' . $referer['port']) : '');
        }
        // 请求来源存在 且 请求非简单请求 且 来源域与请求域不一致，拒绝访问
        if ($referer_url && !$this->isSimpleRequest($request) && $referer_host !== $request->host()) {
            $result = Response::create('405 Not Allowed', '', 405);
        } else {
            $result = $next($request);
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
