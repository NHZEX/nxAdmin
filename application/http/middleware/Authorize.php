<?php
/**
 * Created by PhpStorm.
 * User: NHZEXG
 * Date: 2019/1/8
 * Time: 17:58
 */

namespace app\http\middleware;

use app\common\traits\ShowReturn;
use app\controller\AdminBase;
use app\logic\AdminRole;
use app\logic\Permission as PermissionLogic;
use app\model\AdminUser as AdminUserModel;
use app\model\Permission as PermissionModel;
use facade\WebConv;
use think\facade\Response;
use think\facade\Url;
use think\Request;
use traits\controller\Jump;

class Authorize extends Middleware
{
    use ShowReturn;
    use Jump;

    /** @var \think\App */
    protected $app;

    /**
     * @param Request  $request
     * @param \Closure $next
     * @return \think\response
     * @throws \ReflectionException
     * @throws \app\exception\JsonException
     */
    public function handle(Request $request, \Closure $next)
    {
        //获取调度类
        $transfer_class = self::getCurrentDispatchClass($request);
        $action = $request->action(false);

        if (null === $transfer_class) {
            return $next($request);
        }

        // 计算节点Hash
        $node = PermissionLogic::computeNode($transfer_class, $action);
        // 获取节点标识
        $flag = PermissionLogic::getFlagByHash($node->hash);
        // 忽略权限控制
        if (($flag & PermissionModel::FLAG_LOGIN) === 0) {
            return $next($request);
        }

        // 分析控制器是否继承AdminBase
        $r = new \ReflectionClass($transfer_class);
        $tc = $r->newInstanceWithoutConstructor();
        if (false === $tc instanceof AdminBase) {
            return $next($request);
        }
        unset($r, $tc);

        // 会话权限判断
        if (true !== WebConv::verify(true)) {
            return $this->jump($request, 'Unauthorized:' . WebConv::getErrorMessage());
        }

        $conv = WebConv::getSelf();
        //超级管理员跳过权限限制
        if ($conv->sess_user_genre === AdminUserModel::GENRE_SUPER_ADMIN) {
            return $next($request);
        }

        //角色权限验证
        if (($flag & PermissionModel::FLAG_PERMISSION) > 0) {
            if (false === AdminRole::isPermissionAllowed($conv->sess_role_id, $node->hash)) {
                return Response::create('权限不足', '', 403);
            }
        }

        return $next($request);
    }

    /**
     * 权限检查失败跳转
     * @param Request $request
     * @param         $message
     * @return \think\response
     */
    protected function jump(Request $request, $message)
    {
        if (!$request->isAjax()) {
            // 构建跳转数据
            $jump = base64_encode($request->url(true));
            return $this->error(
                $message,
                '/admin.login?' . http_build_query(['jump' => $jump])
            );
        } else {
            return Response::create($message, '', 401)
                ->header([
                    'Soft-Location' => Url::build('@admin.login')
                ]);
        }
    }

    /**
     * 操作错误跳转的快捷方法
     * @access protected
     * @param  mixed   $msg    提示信息
     * @param  string  $url    跳转的URL地址
     * @param  mixed   $data   返回的数据
     * @param  int     $wait   跳转等待时间
     * @param  array   $header 发送的Header信息
     * @return \think\response
     */
    protected function error($msg = '', $url = null, $data = '', $wait = 3, array $header = [])
    {
        $type = $this->getResponseType();
        if (is_null($url)) {
            $url = $this->app['request']->isAjax() ? '' : 'javascript:history.back(-1);';
        } elseif ('' !== $url) {
            $url = (strpos($url, '://') || 0 === strpos($url, '/')) ? $url : $this->app['url']->build($url);
        }

        $result = [
            'code' => 0,
            'msg'  => $msg,
            'data' => $data,
            'url'  => $url,
            'wait' => $wait,
        ];

        if ('html' == strtolower($type)) {
            $type = 'jump';
        }

        $response = Response::create($result, $type)
            ->header($header)
            ->options(['jump_template' => $this->app['config']->get('dispatch_error_tmpl')]);

        return $response;
    }
}
