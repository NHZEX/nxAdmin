<?php
/**
 * Created by PhpStorm.
 *
 * Date: 2017/11/16
 * Time: 13:52
 */

namespace app\controller\admin;

use app\Logic\AdminRole;
use app\Service\Auth\Annotation\Auth;
use app\Service\Auth\AuthGuard;
use think\Env;
use think\Response;
use function config;
use function env;
use function view_current;

/**
 * Class Main
 * @package app\controller\admin
 */
class Main extends Base
{
    /**
     * 基本系统设置
     */
    public function config()
    {
        return self::showSucceed([
            'webTitle' => env('SYSTEM_WEB_TITLE'),
            'loginCaptcha' => config('captcha.login'),
        ]);
    }

    /**
     * 获取用户信息
     * @Auth()
     * @return Response
     */
    public function userInfo()
    {
        $user = \app\Service\Auth\Facade\Auth::user();
        $role_id = $user->isSuperAdmin() ? -1 : $user->role_id;
        return self::showSucceed([
            'user' => $user,
            'permission' => AdminRole::queryOnlyPermission($role_id),
        ]);
    }

    /**
     * 主页框架
     * @Auth()
     * @param Env       $env
     * @param AuthGuard $authGuard
     * @return mixed
     */
    public function index(Env $env, AuthGuard $authGuard)
    {
        return view_current([
            'info' => [
                'title' => $env->get('system.web_title'),
            ],
            'webmenu' => '{}',
            'user' => $authGuard->user(),
            'url' => [
                'mainpage' => url('sysinfo'),
                'basic_info' => url('@admin.manager/pageEdit', ['base_pkid' => $authGuard->id()]),
                'logout' => url('@admin.login/logout'),
                'clear_cache' => url('clearCache'),
            ],
        ]);
    }

    /**
     * 系统信息页面
     * @Auth()
     * @return string
     */
    public function sysinfo()
    {
        return view_current();
    }
}
