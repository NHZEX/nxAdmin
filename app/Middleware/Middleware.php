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
use think\exception\HttpException;
use think\facade\App;
use think\Request;
use think\Response;

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
        $controller = Util::toSnakeCase($request->controller());
        $transfer_class = App::parseClass('controller', $controller);
        if (!class_exists($transfer_class)) {
            throw new HttpException(404, 'controller not exists:' . $transfer_class);
        }

        return $transfer_class;
    }
}
