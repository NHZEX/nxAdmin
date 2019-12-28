<?php
/**
 * Created by PhpStorm.
 *
 * Date: 2017/11/16
 * Time: 13:51
 */

namespace app\controller\admin;

use app\Logic\AdminUser;
use app\Service\Auth\AuthGuard;
use app\Service\Auth\Facade\Auth;
use Captcha\Captcha;
use think\facade\Session;
use think\Response;
use think\response\View;
use function env;
use function hash_hmac;
use function view_current;

class Login extends Base
{
    /**
     * @param AuthGuard   $auth
     * @param string|null $jump
     * @return Response|View
     */
    public function index(AuthGuard $auth, ?string $jump = null)
    {
        $jump_url = $this->request->header('Referer', false);
        // 如果验证成功直接跳转到主页
        if ($auth->check()) {
            return self::show302($jump_url ?: url('@admin.main'));
        }

        // 生成登陆成功后跳转目的地（url传入/主页）
        $jump_url = $jump ? rawurldecode($jump) : ($jump_url ?: url('@admin.main'));
        if (strpos($jump_url, 'logout') > 0) {
            $jump_url = url('@admin.main');
        }

        $loginToken = get_rand_str(32);

        // 模板渲染
        return view_current([
            'url_login' => url('login', ['_' => crc32($loginToken)], false),
            'url_check' => url('check', [], false),
            'url_captcha' => url('captcha', ['_' => $loginToken], false),
            'url_jump' => $jump_url,

            'login_token' => $loginToken,
            'auto_login_name' => 'login_time',
        ]);
    }

    /**
     * 会话有效性检查
     * @param AuthGuard $auth
     * @return Response
     */
    public function check(AuthGuard $auth)
    {
        if ($auth->check()) {
            return self::showMsg(CODE_SUCCEED);
        } else {
            return self::showMsg(CODE_CONV_VERIFY);
        }
    }

    /**
     * 产生一个验证码
     * @param string|null $_
     * @return Response
     */
    public function captcha(string $_ = null)
    {
        if (!$_) {
            abort(401);
        }
        $captcha = new Captcha($this->app->config->get('captcha'));
        $captcha->entry();
        $captcha->saveToRedis($_);
        return $captcha->send();
    }

    /**
     * 登陆
     * @param AdminUser $adminUser
     * @return Response
     */
    public function login(AdminUser $adminUser)
    {
        $param = $this->request->param();

        // 获取令牌
        $ctoken = $param['#'];

        // 验证码校验
        if ($this->app->config->get('captcha.login')) {
            $captcha = new Captcha($this->app->config->get('captcha'));
            if (!$captcha->checkToRedis($ctoken, $param['captcha'] ?? '0000')) {
                return self::showMsg(CODE_COM_CAPTCHA, $captcha->getMessage());
            }
        }

        // 参数提取
        isset($param['lasting']) ?: $param['lasting'] = false;
        ['account' => $account, 'password' => $password, 'lasting' => $rememberme] = $param;

        // 执行登陆操作
        if ($adminUser->login($adminUser::LOGIN_TYPE_NAME, $account, $password, $rememberme)) {
            $this->app->cookie->set('login_time', time() + 10);
            return self::showSucceed([
                'uuid' => hash_hmac('sha1', Auth::id(), env('DEPLOY_SECURITY_SALT')),
                'token' => Session::getId(),
            ]);
        } else {
            return self::showMsg(CODE_CONV_LOGIN, $adminUser->getErrorMessage());
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
            $this->app->cookie->delete('login_time');
            $auth->logout();
        }
        if ($this->request->isAjax()) {
            return self::showSucceed();
        }
        return $this->success('退出登陆', '@admin.login/index');
    }
}
