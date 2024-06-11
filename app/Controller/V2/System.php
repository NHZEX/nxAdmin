<?php

namespace app\Controller\V2;

use app\Controller\ApiBase;
use app\Logic\SystemLogic;
use app\ReplyEx;
use app\Service\Auth\AuthHelper;
use app\Service\DebugHelper\EnvironmentHelper;
use app\Service\System\DatabaseUtils;
use think\Response;
use Zxin\Think\Auth\Annotation\Auth;
use Zxin\Think\Route\Annotation\Group;
use Zxin\Think\Route\Annotation\Route;

#[Group('v2/system')]
class System extends ApiBase
{
    /**
     * 基本系统设置.
     */
    #[Route(method: 'GET')]
    public function config(): Response
    {
        return ReplyEx::success([
            'webTitle' => env('SYSTEM_WEB_TITLE'),
            'loginCaptcha' => $this->app->config->get('feature.login_captcha'),
        ]);
    }

    #[Auth]
    #[Route(method: 'GET')]
    public function info(): Response
    {
        $user = AuthHelper::user();
        $user->hidden([
            'role', 'password', 'remember', 'last_login_ip',
            'delete_time', 'group_id', 'lock_version', 'signup_ip',
        ]);

        return ReplyEx::success([
            'user' => $user,
            'permission' => $user->getUnfoldPermission(),
        ]);
    }

    #[Auth()]
    #[Route(method: 'GET')]
    public function sysinfo(): Response
    {
        return ReplyEx::success(EnvironmentHelper::getEnvInfo());
    }

    #[Auth('admin')]
    #[Route(method: 'GET')]
    public function database(): Response
    {
        $list = DatabaseUtils::queryTabelInfo(newStructure: true);

        return ReplyEx::success($list);
    }

    /**
     * 重置缓存.
     */
    #[Auth('admin.resetCache')]
    #[Route(method: 'POST')]
    public function resetCache(): Response
    {
        SystemLogic::resetPermissionCache();

        return ReplyEx::success();
    }
}
