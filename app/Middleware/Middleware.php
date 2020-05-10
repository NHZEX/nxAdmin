<?php

namespace app\Middleware;

use Closure;
use think\App;
use think\exception\HttpException;
use think\Request;
use think\Response;

abstract class Middleware
{
    /**
     * @var App
     */
    protected $app;

    public function __construct(App $app)
    {
        $this->app = $app;
    }

    /**
     * @param Request  $request
     * @param Closure $next
     * @return Response|string
     */
    abstract public function handle(Request $request, Closure $next);

    /**
     * 获取节点名称
     * @param Request $request
     * @return string
     */
    protected function getNodeName(Request $request): ?string
    {
        if (empty($request->controller() . $request->action())) {
            return null;
        }
        $appName = $this->app->http->getName();
        $appName = $appName ? ($appName . '/') : '';
        return $appName . $request->controller(true) . '/' . $request->action(true);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    protected function getControllerClassName(Request $request) :?string
    {
        $suffix = $this->app->route->config('controller_suffix') ? 'Controller' : '';
        $controllerLayer = $this->app->route->config('controller_layer') ?: 'controller';

        $name = $request->controller();
        $class = $this->app->parseClass($controllerLayer, $name . $suffix);
        if (!class_exists($class)) {
            throw new HttpException(404, 'controller not exists:' . $class);
        }

        return $class;
    }
}
