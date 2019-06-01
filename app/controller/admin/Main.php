<?php
/**
 * Created by PhpStorm.
 *
 * Date: 2017/11/16
 * Time: 13:52
 */

namespace app\controller\admin;

use app\Facade\WebConv;
use app\Logic\SystemMenu;
use app\Model\AdminUser;
use think\facade\Env;
use think\facade\View;

/**
 * Class Main
 * @package app\controller\admin
 */
class Main extends Base
{
    /**
     * 主页框架
     * @return mixed
     * @throws \app\Exception\JsonException
     * @throws \think\exception\DbException
     */
    public function index()
    {
        View::assign('info', [
            'title' => Env::get('system.web_title')
        ]);
        View::assign('webmenu', $this->getMenuToJson());
        View::assign('user', WebConv::getAdminUser());

        View::assign('url', [
            'mainpage' => url('sysinfo'),
            'logout' => url('@admin.login/logout'),
        ]);
        return View::fetch();
    }

    /**
     * @return string
     * @throws \app\Exception\JsonException
     * @throws \think\exception\DbException
     */
    private function getMenuToJson()
    {
        //超级管理员不限制菜单
        if (WebConv::getSelf()->sess_user_genre === AdminUser::GENRE_SUPER_ADMIN) {
            $menus = SystemMenu::obtainMenus();
        } else {
            $menus = SystemMenu::obtainMenus(WebConv::getSelf()->sess_role_id);
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
     * @return mixed
     */
    public function sysinfo()
    {
        return View::fetch();
    }
}
