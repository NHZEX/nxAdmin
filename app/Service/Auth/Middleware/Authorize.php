<?php
declare(strict_types=1);

namespace app\Service\Auth\Middleware;

use app\Service\Auth\AuthGuard;
use app\Service\Auth\Permission;
use Closure;
use think\App;
use think\Request;
use think\Response;
use think\response\View;

class Authorize
{
    /**
     * @var App
     */
    private $app;
    /**
     * @var AuthGuard
     */
    private $auth;
    /**
     * @var Permission
     */
    private $permission;

    public function __construct(App $app, AuthGuard $auth, Permission $permission)
    {
        $this->app = $app;
        $this->auth = $auth;
        $this->permission = $permission;
    }

    /**
     * @param Request  $request
     * @param Closure $next
     * @return Response|string
     */
    public function handle(Request $request, Closure $next)
    {
        $nodeName = $this->getNodeName($request);

        if (null === $nodeName) {
            return $next($request);
        }

        $nodeControl = $this->permission->queryNode($nodeName);

        if (null === $nodeControl) {
            return $next($request);
        }

        // 会话权限判断
        if (true !== $this->auth->check()) {
            $this->app->cookie->delete('login_time');
            return $this->jump($request, '请重新登录');
        }

        // 超级管理员跳过权限限制
        if ($this->auth->user()->isSuperAdmin()) {
            return $next($request);
        }

        // 权限判定
        if (!$this->auth->can('node@' . $nodeName)) {
            return Response::create('权限不足', 'html', 403);
        }

        return $next($request);
    }

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
     * 权限检查失败跳转
     * @param Request $request
     * @param         $message
     * @return response
     */
    protected function jump(Request $request, $message)
    {
        if (!$request->isAjax()) {
            // 构建跳转数据
            $jump = rawurlencode($request->url(true));
            return $this->error(
                $message,
                '/admin.login?' . http_build_query(['jump' => $jump])
            );
        } else {
            return Response::create($message, 'html', 401)
                ->header([
                    'Soft-Location' => $this->app->route->buildUrl('@admin.login')
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
     * @return response
     */
    protected function error($msg = '', $url = null, $data = '', $wait = 3, array $header = [])
    {
        $type = ($this->app->request->isJson() || $this->app->request->isAjax()) ? 'json' : 'html';
        if (is_null($url)) {
            $url = $this->app->request->isAjax() ? '' : 'javascript:history.back(-1);';
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
            /** @var View $respView */
            $respView = Response::create('/dispatch_jump', 'view');
            $response = $respView->assign($result);
        } else {
            $response = Response::create($result, $type)
                ->header($header)
                ->options(['jump_template' => $this->app->config->get('app.dispatch_error_tmpl')]);
        }

        return $response;
    }
}
