<?php
/**
 * Created by PhpStorm.
 * User: NHZEXG
 * Date: 2019/1/16
 * Time: 11:31
 */

namespace app\Middleware;

use Closure;
use HZEX\Util;
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
        $controller = Util::toSnakeCase($request->controller());
        $transfer_class = $this->app->parseClass('controller', $controller);
        if (!class_exists($transfer_class)) {
            throw new HttpException(404, 'controller not exists:' . $transfer_class);
        }

        return $transfer_class;
    }
}
