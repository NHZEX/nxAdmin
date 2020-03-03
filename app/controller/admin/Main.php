<?php
/**
 * Created by PhpStorm.
 *
 * Date: 2017/11/16
 * Time: 13:52
 */

namespace app\controller\admin;

use app\Service\Auth\Annotation\Auth;
use app\Service\Auth\AuthGuard;
use think\Env;
use function view_current;

/**
 * Class Main
 * @package app\controller\admin
 */
class Main extends Base
{
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
