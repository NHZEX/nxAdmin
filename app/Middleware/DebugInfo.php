<?php
declare(strict_types=1);

namespace app\Middleware;

use Closure;
use think\Request;
use think\Response;

class DebugInfo extends Middleware
{

    /**
     * @param Request $request
     * @param Closure $next
     * @return Response|string
     */
    public function handle(Request $request, Closure $next)
    {
        // 记录路由和请求信息
        if ($this->app->isDebug()) {
            $app = $request->app() ?: 'default';
            $controller = $request->controller(true);
            $class = $this->app->parseClass('controller', $controller);
            $action = $request->action();
            $this->app->log->info('[ DISPATCH ] ' . $app . '-' . $class . '#' . $action);
            // $this->app->log->info('[ ROUTE ] ' . var_export($request->rule()->__debugInfo(), true));
            $this->app->log->info('[ HEADER ] ' . var_export($request->header(), true));
            $this->app->log->info('[ PARAM ] ' . var_export($request->param(), true));
        }
        return $next($request);
    }
}
