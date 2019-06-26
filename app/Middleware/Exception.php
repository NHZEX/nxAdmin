<?php
/**
 * Created by PhpStorm.
 * User: NHZEXG
 * Date: 2019/3/2
 * Time: 10:16
 * @noinspection PhpRedundantCatchClauseInspection
 */

namespace app\Middleware;

use app\Exception\AccessControl;
use app\Traits\ShowReturn;
use Closure;
use think\Request;
use think\Response;
use Tp\Model\Exception\ModelException;

class Exception extends Middleware
{
    use ShowReturn;

    /**
     * @param Request $request
     * @param Closure $next
     * @return Response|string
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            $result = $next($request);
        } catch (ModelException | AccessControl $e) {
            $result = self::showException($e);
        }
        return $result;
    }
}
