<?php
declare(strict_types=1);

namespace app\Service\Auth\Middleware;

use app\Traits\JumpHelper;
use Closure;
use think\App;
use think\Request;
use think\Response;
use Zxin\Think\Auth\AuthGuard;
use Zxin\Think\Auth\Permission;
use function func\reply\reply_bad;

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
        $nodeUrl = $this->getNodeName($request);

        if (null === $nodeUrl) {
            return $next($request);
        }
        $nodeUrl = "node@{$nodeUrl}";

        $nodeControl = $this->permission->queryFeature($nodeUrl);

        if (null === $nodeControl) {
            return $next($request);
        }

        // 会话权限判断
        if (true !== $this->auth->check()) {
            $this->auth->logout();
            $msg = $this->auth->getMessage();
            $msg = empty($msg) ? '会话无效' : ('会话无效: ' . $msg);
            return $this->failJump($request, $msg);
        }
        // 权限判定
        if (!$this->auth->gate()->check($nodeUrl, $request)) {
            $response = $this->refuseJump($request, '权限不足');
        } else {
            $response = $next($request);
        }
//        /** @var \think\Middleware $middleware */
//        $middleware = $this->app->get('middleware');
//        $middleware->handleException();
//        AuthContext::get()->getPermissionsDetails();
//        log_debug(AuthContext::get());
        // 使用记住我恢复登录状态
        if ($this->auth->viaRemember()) {
            $response->header([
                'X-Uuid' => $this->auth->getHashId(),
                'X-Token' => $this->app->session->getId(),
            ]);
        }

        return $response;
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
    protected function failJump(Request $request, $message): Response
    {
        if ($request->isAjax()) {
            return reply_bad(null, null, null, 401);
        } else {
            return $this->error($message, null, null, 3600);
        }
    }

    /**
     * @param Request $request
     * @param         $message
     * @return Response
     */
    protected function refuseJump(Request $request, $message): Response
    {
        if ($request->isAjax()) {
            return reply_bad(null, $message, null, 403);
        } else {
            return Response::create($message, 'html', 403);
        }
    }
}
