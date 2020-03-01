<?php
/**
 * Created by PhpStorm.
 *
 * Date: 2017/11/16
 * Time: 13:52
 */

namespace app\controller\admin;

use app\Exception\JsonException;
use app\Logic\AdminRole;
use app\Logic\SystemMenu;
use app\Service\Auth\Annotation\Auth;
use app\Service\Auth\AuthGuard;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
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
     * @throws DataNotFoundException
     * @throws DbException
     * @throws JsonException
     * @throws ModelNotFoundException
     */
    public function index(Env $env, AuthGuard $authGuard)
    {
        return view_current([
            'info' => [
                'title' => $env->get('system.web_title'),
            ],
            'webmenu' => $this->getMenuToJson($authGuard),
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
     * @param AuthGuard $authGuard
     * @return string
     * @throws DataNotFoundException
     * @throws DbException
     * @throws JsonException
     * @throws ModelNotFoundException
     */
    private function getMenuToJson(AuthGuard $authGuard)
    {
        $user = $authGuard->user();
        //超级管理员不限制菜单
        if ($user->isSuperAdmin()) {
            $menus = SystemMenu::obtainMenus();
        } else {
            $menus = SystemMenu::obtainMenus($user->role_id);
        }
        return json_encode_throw_on_error($menus);
    }

    /**
     * 菜单结构生成器
     * @param array $list
     * @param string $id
     * @return array
     */
    protected function menu($list = [], $id = 'R')
    {
        if (false === is_array($list)) {
            return [];
        }
        $menu = [];

        foreach ($list as $key => $item) {
            $id .= "-$key";
            $children = isset($item['children']) ? $this->menu($item['children'], $id) : [];

            $menu[] = [
                'id' => $id,
                'title' => $item['title'],
                'icon' => $item['icon'],
                'spread' => $item['spread'] ?? false,
                'url' => $item['url'] ?? null,
                'children' => $children,
            ];
        }
        return $menu;
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

    /**
     * 清理缓存
     * @Auth(policy="userType:admin")
     * @return Response
     */
    public function clearCache()
    {
        SystemMenu::refreshCache();
        return self::showSucceed();
    }
}
