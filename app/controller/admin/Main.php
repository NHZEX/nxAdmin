<?php
/**
 * Created by PhpStorm.
 *
 * Date: 2017/11/16
 * Time: 13:52
 */

namespace app\controller\admin;

use app\Exception\JsonException;
use app\Facade\WebConv;
use app\Logic\Permission;
use app\Logic\SystemMenu;
use app\Model\AdminUser;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\Env;
use think\Response;
use function view_current;

/**
 * Class Main
 * @package app\controller\admin
 */
class Main extends Base
{
    /**
     * 主页框架
     * @param Env $env
     * @return mixed
     * @throws DataNotFoundException
     * @throws DbException
     * @throws JsonException
     * @throws ModelNotFoundException
     */
    public function index(Env $env)
    {
        return view_current([
            'info' => [
                'title' => $env->get('system.web_title'),
            ],
            'webmenu' => $this->getMenuToJson(),
            'user' => WebConv::getConvUser(),
            'url' => [
                'mainpage' => url('sysinfo'),
                'basic_info' => url('@admin.manager/pageEdit', ['base_pkid' => WebConv::getConvUser()->id]),
                'logout' => url('@admin.login/logout'),
                'clear_cache' => url('clearCache'),
            ],
        ]);
    }

    /**
     * @return string
     * @throws JsonException
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    private function getMenuToJson()
    {
        //超级管理员不限制菜单
        if (WebConv::getUserGenre() === AdminUser::GENRE_SUPER_ADMIN) {
            $menus = SystemMenu::obtainMenus();
        } else {
            $menus = SystemMenu::obtainMenus(WebConv::getRoleId());
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
     * @return string
     */
    public function sysinfo()
    {
        return view_current();
    }

    /**
     * 清理缓存
     * @return Response
     */
    public function clearCache()
    {
        SystemMenu::refreshCache();
        Permission::refreshCache();
        return self::showSucceed();
    }
}
