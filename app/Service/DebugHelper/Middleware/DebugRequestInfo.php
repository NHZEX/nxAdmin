<?php
declare(strict_types=1);

namespace app\Service\DebugHelper\Middleware;

use Closure;
use think\App;
use think\Request;
use think\Response;

class DebugRequestInfo
{
    protected $app;

    public function __construct(App $app)
    {
        $this->app = $app;
    }

    /**
     * @param Request $request
     * @param Closure $next
     * @return Response|string
     */
    public function handle(Request $request, Closure $next)
    {
        // 记录路由和请求信息
        $appName = $this->app->http->getName() ?: 'empty';
        $controller = $request->controller() ?: 'unknown';
        $action = $request->action() ?: 'unknown';
        $this->app->log->record("dispatch: {$appName}-{$controller}-{$action}", 'route');
        // $app->log->info('[ ROUTE ] ' . var_export($request->rule()->__debugInfo(), true));
        $this->app->log->record('header: ' . var_export($request->header(), true), 'request');
        $this->app->log->record('param: ' . var_export($request->param(), true), 'request');
        return $next($request);
    }
}
