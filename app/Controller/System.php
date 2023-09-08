<?php

namespace app\Controller;

use app\Logic\SystemLogic;
use app\Service\System\DatabaseUtils;
use app\Utils;
use think\middleware\Throttle;
use think\Response;
use Util\Reply;
use Zxin\Captcha\Captcha;
use Zxin\Think\Auth\Annotation\Auth;
use Zxin\Think\Route\Annotation\Group;
use Zxin\Think\Route\Annotation\Middleware;
use Zxin\Think\Route\Annotation\Route;

#[Group('system')]
class System extends ApiBase
{
    /**
     * 基本系统设置
     */
    #[Route(method: 'GET')]
    public function config(): Response
    {
        return Reply::success([
            'webTitle' => env('SYSTEM_WEB_TITLE'),
            'loginCaptcha' => $this->app->config->get('feature.login_captcha'),
        ]);
    }

    #[Auth()]
    #[Route(method: 'GET')]
    public function sysinfo(): Response
    {
        return Reply::success(Utils::getEnvInfo());
    }

    #[Auth('admin')]
    #[Route(method: 'GET')]
    public function database(): Response
    {
        $list = DatabaseUtils::queryTabelInfo();

        return Reply::success($list);
    }

    /**
     * 获取一个验证码
     */
    #[Route(method: 'GET', middleware: [])]
    #[Middleware(Throttle::class, [
        ['visit_rate' => CAPTCHA_THROTTLE_RATE],
    ])]
    public function captcha(Captcha $captcha): Response
    {
        $captcha->entry();
        return $captcha->sendResponse([
            'X-Captcha-Token' => $captcha->getValidator()->generateToken(),
        ]);
    }

    /**
     * 重置缓存
     */
    #[Auth("admin.resetCache")]
    #[Route(method: 'GET')]
    public function resetCache(SystemLogic $logic): Response
    {
        $logic->resetPermissionCache();
        return Reply::success();
    }
}
