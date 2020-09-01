<?php

namespace app\controller\api\admin;

use app\Logic\AdminRole;
use app\Logic\AdminUser;
use app\Service\Auth\Facade\Auth as AuthFacade;
use Captcha\Captcha;
use think\facade\Session;
use think\Response;
use think\response\View;
use Zxin\Think\Auth\Annotation\Auth;
use Zxin\Think\Auth\AuthGuard;
use function func\reply\reply_bad;
use function func\reply\reply_succeed;

class Index extends Base
{
    /**
     * 登陆
     * @param AdminUser $adminUser
     * @param Captcha   $captcha
     * @return Response
     */
    public function login(AdminUser $adminUser, Captcha $captcha)
    {
        $param = $this->request->param();

        // 获取令牌
        $ctoken = $param['token'];

        // 验证码校验
        if ($captcha->login) {
            if (!$captcha->verifyToken($ctoken, $param['captcha'] ?? '0000')) {
                return reply_bad(CODE_COM_CAPTCHA, $captcha->getMessage());
            }
        }

        // 参数提取
        isset($param['lasting']) ?: $param['lasting'] = false;
        ['account' => $account, 'password' => $password, 'lasting' => $rememberme] = $param;

        // 执行登陆操作
        if ($adminUser->login($adminUser::LOGIN_TYPE_NAME, $account, $password, $rememberme)) {
            return reply_succeed([
                'uuid' => $adminUser->getAuth()->getHashId(),
                'token' => Session::getId(),
            ]);
        } else {
            return reply_bad(CODE_CONV_LOGIN, $adminUser->getErrorMessage());
        }
    }

    /**
     * 退出登陆
     * @param AuthGuard $auth
     * @return Response|View
     */
    public function logout(AuthGuard $auth)
    {
        if ($auth->check()) {
            $auth->logout();
        }

        return reply_succeed();
    }

    /**
     * 获取用户信息
     * @Auth()
     * @return Response
     */
    public function userInfo()
    {
        $user = AuthFacade::user();
        $user->hidden([
            'role', 'password', 'remember', 'last_login_ip',
            'delete_time', 'group_id', 'lock_version', 'signup_ip',
        ]);
        $role_id = $user->isSuperAdmin() ? -1 : $user->role_id;
        return reply_succeed([
            'user' => $user,
            'permission' => AdminRole::queryOnlyPermission($role_id),
        ]);
    }
}
