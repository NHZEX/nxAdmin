<?php

namespace app\Controller\admin;

use app\Logic\AdminUser;
use app\Service\Auth\AuthHelper;
use think\facade\Session;
use think\Response;
use Util\Reply;
use Zxin\Captcha\Captcha;
use Zxin\Think\Auth\Annotation\Auth;
use Zxin\Think\Auth\AuthGuard;
use Zxin\Think\Route\Annotation\Group;
use Zxin\Think\Route\Annotation\Route;
use Zxin\Think\Validate\Annotation\Validation;

#[Group('admin', registerSort: 3000)]
class Index extends Base
{
    /**
     * 登陆
     */
    #[Validation("@Login")]
    #[Route(method: 'POST')]
    public function login(AdminUser $adminUser, Captcha $captcha): Response
    {
        $param = $this->request->param();

        // 获取令牌
        $ctoken = $param['token'];

        // 验证码校验
        if ($this->app->config->get('feature.login_captcha')) {
            $validator = $captcha->getValidator();
            if (!$validator->verifyToken($ctoken, $param['captcha'] ?? '0000')) {
                return Reply::bad(CODE_COM_CAPTCHA, $validator->getMessage());
            }
        }

        // 参数提取
        isset($param['lasting']) || ($param['lasting'] = false);
        ['account' => $account, 'password' => $password, 'lasting' => $rememberme] = $param;

        // 执行登陆操作
        if ($adminUser->login($adminUser::LOGIN_TYPE_NAME, $account, $password, $rememberme)) {
            return Reply::success([
                'uuid' => $adminUser->getAuth()->getHashId(),
                'token' => Session::getId(),
            ]);
        } else {
            return Reply::bad(CODE_CONV_LOGIN, $adminUser->getErrorMessage());
        }
    }

    /**
     * 退出登陆
     */
    #[Route(method: 'GET')] // 应该淘汰 GET 吧
    public function logout(AuthGuard $auth): Response
    {
        if ($auth->check()) {
            $auth->logout();
        }

        return Reply::success();
    }

    /**
     * 获取用户信息
     */
    #[Auth]
    #[Route('user-info', method: 'GET')]
    public function userInfo(): Response
    {
        $user = AuthHelper::user();
        $user->hidden([
            'role', 'password', 'remember', 'last_login_ip',
            'delete_time', 'group_id', 'lock_version', 'signup_ip',
        ]);
        return Reply::success([
            'user' => $user,
            'permission' => $user->getUnfoldPermission(),
        ]);
    }
}
