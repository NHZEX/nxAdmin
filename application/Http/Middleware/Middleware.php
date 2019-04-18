<?php
/**
 * Created by PhpStorm.
 * User: NHZEXG
 * Date: 2019/1/16
 * Time: 11:31
 */

namespace app\Http\Middleware;

use Closure;
use think\exception\HttpException;
use think\facade\App;
use think\facade\Config;
use think\Request;
use think\Response;
use think\route\dispatch;

abstract class Middleware
{
    /**
     * @param Request  $request
     * @param Closure $next
     * @return Response|string
     */
    abstract public function handle(Request $request, Closure $next);

    /**
     * @param Request $request
     * @return mixed
     */
    protected function getCurrentDispatchClass(Request $request) :?string
    {
        $dispatch = $request->dispatch();
        $dispatch_calss_name = get_class($dispatch);

        // 分析当前调度
        switch ($dispatch_calss_name) {
            case dispatch\Module::class:
                /** @var dispatch\Module $dispatch */
                // 分析当前调用类
                $result = $dispatch->getDispatch();
                $controller = empty($result[1]) ? Config::get('app.default_controller') : $result[1];
                $transfer_class = App::parseClass($request->module(), 'controller', $controller, false);
                if (!class_exists($transfer_class)) {
                    throw new HttpException(404, 'controller not exists:' . $transfer_class);
                }

                return $transfer_class;
            case dispatch\Controller::class:
            case dispatch\Callback::class:
            case dispatch\Redirect::class:
            case dispatch\Response::class:
            case dispatch\Url::class:
            case dispatch\View::class:
                return null;
        }

        return null;
    }
}
