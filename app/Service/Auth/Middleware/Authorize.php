<?php
declare(strict_types=1);

namespace app\Service\Auth\Middleware;

use app\Service\Auth\AuthGuard;
use app\Service\Auth\Permission;
use app\Traits\JumpHelper;
use Closure;
use think\App;
use think\Request;
use think\Response;

class Authorize
{
    use JumpHelper;

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

        $nodeControl = $this->permission->queryFeature('node@' . $nodeName);

        if (null === $nodeControl) {
            return $next($request);
        }

        // 会话权限判断
        if (true !== $this->auth->check()) {
            $msg = $this->auth->getMessage();
            $msg = empty($msg) ? '会话无效' : ('会话无效: ' . $msg);
            return $this->failJump($request, $msg);
        }
        // 超级管理员跳过鉴权
        if ($this->auth->user()->isSuperAdmin()) {
            return $next($request);
        }

        // 权限判定
        if (!$this->auth->gate()->check('node@' . $nodeName, $request)) {
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
    protected function failJump(Request $request, $message)
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
}
