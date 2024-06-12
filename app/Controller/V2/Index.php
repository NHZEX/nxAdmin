<?php

declare(strict_types=1);

namespace app\Controller\V2;

use app\Controller\ApiBase;
use app\Logic\AdminUser;
use app\ReplyEx;
use app\Validate\Login;
use think\middleware\Throttle;
use think\Response;
use think\Session;
use Zxin\Captcha\Captcha;
use Zxin\Think\Auth\AuthGuard;
use Zxin\Think\Route\Annotation\Middleware;
use Zxin\Think\Route\Annotation\Route;
use Zxin\Think\Validate\Annotation\Validation;

class Index extends ApiBase
{
    /**
     * 获取一个验证码
     */
    #[Route('v2/captcha', method: 'GET', middleware: [])]
    #[Middleware(Throttle::class, [
        ['visit_rate' => CAPTCHA_THROTTLE_RATE],
    ])]
    public function captcha(Captcha $captcha): Response
    {
        $captcha->entry();
        $headers = [
            'X-Captcha-Token' => $captcha->getValidator()->generateToken(),
        ];
        if (is_debug_demo()) {
            $headers['X-Test-Captcha-Code'] = $captcha->getCodePlaintext();
        }

        return $captcha->sendResponse($headers);
    }

    #[Route('v2/login', method: 'GET')]
    public function loginConfig(): Response
    {
        return ReplyEx::success([
            'enableCaptcha' => $this->app->config->get('feature.login_captcha'),
        ]);
    }

    #[Validation(Login::class)]
    #[Route('v2/login', method: 'POST')]
    public function login(AdminUser $adminUser, Captcha $captcha, Session $session): Response
    {
        $param = $this->request->param();

        // 验证码校验
        if ($this->app->config->get('feature.login_captcha')) {
            if (empty($param['token'])) {
                return ReplyEx::bad('缺少验证码参数', CODE_COM_CAPTCHA);
            }
            $ctoken = $param['token'];
            $validator = $captcha->getValidator();
            // 老系统兼容获取
            $code = $param['code'] ?? ($param['captcha'] ?? '0000');
            if (!$validator->verifyToken($ctoken, $code)) {
                return ReplyEx::bad($validator->getMessage(), CODE_COM_CAPTCHA);
            }
        }

        // 参数提取
        $param['lasting'] ??= false;
        ['username' => $username, 'password' => $password, 'lasting' => $rememberme] = $param;

        // 执行登陆操作
        if ($adminUser->login($adminUser::LOGIN_TYPE_NAME, $username, $password, $rememberme)) {
            return ReplyEx::success([
                'uuid' => $adminUser->getAuth()->getHashId(),
                'token' => $session->getId(),
            ]);
        } else {
            return ReplyEx::bad($adminUser->getErrorMessage(), CODE_CONV_LOGIN, httpCode: 401);
        }
    }

    #[Route('v2/logout', method: 'GET')]
    public function logout(AuthGuard $auth): Response
    {
        if ($auth->check()) {
            $auth->logout();
        }

        return ReplyEx::success();
    }
}
