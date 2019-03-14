<?php
/**
 * Created by PhpStorm.
 *
 * Date: 2017/11/16
 * Time: 13:51
 */

namespace app\controller\admin;

use app\logic\AdminUser;
use captcha\Captcha;
use facade\WebConv;
use think\exception\DbException;
use think\facade\Config;
use think\facade\Cookie;
use think\facade\Url;

class Login extends Base
{

    /**
     * @param null $jump
     * @return mixed
     * @throws \db\exception\ModelException
     */
    public function index($jump = null)
    {
        $jump_url = $this->request->header('Referer', false);
        // 如果验证成功直接跳转到主页
        if (WebConv::verify()) {
            $this->redirect($jump_url ?: '@admin.main');
        } else {
            if ((new AdminUser())->testRemember()) {
                $this->success('自动登陆成功', $jump_url ?: '@admin.main');
            }
        }

        // 生成登陆成功后跳转目的地（url传入/主页）
        $jump_url = $jump ? base64_decode($jump) : ($jump_url ?: Url::build('@admin.main'));
        false !== strpos($jump_url, 'admin.login/logout') && $jump_url = Url::build('@admin.main');

        $loginToken = get_rand_str(32);

        // 生成主页请求URL
        $this->assign([
            'url_login' => Url::build('login', ['_' => crc32($loginToken)], false),
            'url_check' => Url::build('check', [], false),
            'url_captcha' => Url::build('captcha', ['_' => $loginToken], false),
            'url_jump' => $jump_url,

            'login_token' => $loginToken,
            'cookid_name_by_conv_token' => Cookie::prefix() . WebConv::getSelf()::COOKIE_CONV_TOKEN,
        ]);

        // 模板渲染
        return $this->fetch();
    }

    /**
     * 会话有效性检查
     * @return \think\Response
     */
    public function check()
    {
        if (WebConv::verify()) {
            return self::showMsg(CODE_SUCCEED);
        } else {
            return self::showMsg(CODE_CONV_VERIFY);
        }
    }

    /**
     * 产生一个验证码
     * @param string|null $_
     * @return Captcha
     * @throws \Exception
     */
    public function captcha(string $_ = null)
    {
        if (!$_) {
            abort(404);
        }
        $captcha = new Captcha(Config::pull('captcha'));
        $captcha->entry();
        $captcha->saveToRedis($_);
        return $captcha->send();
    }

    /**
     * 登陆
     * @param AdminUser     $adminUser
     * @return \think\Response
     * @throws \think\Exception
     */
    public function login(
        AdminUser $adminUser
    ) {
        $param = $this->request->param();

        // 获取令牌
        $ctoken = $param['#'];

        // 验证码校验
        $captcha = new Captcha(Config::pull('captcha'));
        if (!$captcha->checkToRedis($ctoken, $param['captcha'] ?? '0000')) {
            return self::showMsg(CODE_COM_CAPTCHA, $captcha->getMessage());
        }

        // 参数提取
        isset($param['lasting']) ?: $param['lasting'] = false;
        ['account' => $account, 'password' => $password, 'lasting' => $rememberme] = $param;

        // 执行登陆操作
        if ($adminUser->login($adminUser::LOGIN_TYPE_NAME, $account, $password, $rememberme)) {
            return self::showMsg(CODE_SUCCEED);
        } else {
            return self::showMsg(CODE_CONV_LOGIN, $adminUser->getErrorMessage());
        }
    }

    /**
     * 退出登陆
     * @throws DbException
     */
    public function logout()
    {
        WebConv::destroy(true);
        $this->success('退出登陆', '@admin.login');
    }
}
