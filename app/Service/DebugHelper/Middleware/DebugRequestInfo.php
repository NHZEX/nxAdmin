<?php
declare(strict_types=1);

namespace app\Service\DebugHelper\Middleware;

use Closure;
use think\App;
use think\Request;
use think\Response;

class DebugRequestInfo
{
    /**
     * @param Request $request
     * @param Closure $next
     * @param App     $app
     * @return Response|string
     */
    public function handle(Request $request, Closure $next, App $app)
    {
        // 记录路由和请求信息
        $appName = $request->app() ?: 'empty';
        $controller = $request->controller(true);
        $class = $app->parseClass('controller', $controller);
        $action = $request->action();
        $app->log->info("[ DISPATCH ] {$appName}-{$class}#{$action}");
        // $app->log->info('[ ROUTE ] ' . var_export($request->rule()->__debugInfo(), true));
        $app->log->info('[ HEADER ] ' . var_export($request->header(), true));
        $app->log->info('[ PARAM ] ' . var_export($request->param(), true));
        return $next($request);
    }
}
