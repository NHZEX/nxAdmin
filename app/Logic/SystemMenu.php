<?php
/**
 * Created by PhpStorm.
 * User: NHZEXG
 * Date: 2019/3/6
 * Time: 17:00
 */

namespace app\Logic;

use app\Exception\JsonException;
use app\Model\System;
use app\Model\SystemMenu as SystemMenuModel;
use Exception;
use Symfony\Component\VarExporter\Exception\ExceptionInterface;
use Symfony\Component\VarExporter\VarExporter;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\facade\App;
use think\facade\Cache;

/**
 * Class SystemMenu
 * @package app\logic
 */
class SystemMenu extends Base
{
    public static $CACHE_KEY_MENUS_ALL = 'system:menu:all';
    public static $CACHE_KEY_MENUS_MAPPING_NODE = 'system:menu:mapping-node';

    /**
     * 查询全部菜单
     * @param bool $force
     * @return array
     */
    public static function queryMenus(bool $force = false): array
    {
        if (!$force && Cache::has(self::$CACHE_KEY_MENUS_ALL)) {
            $menus = Cache::get(self::$CACHE_KEY_MENUS_ALL);
        } else {
            $menus = SystemMenuModel::getArrayTree();
            // 初始化菜单URL
            array_walk_recursive($menus, function (&$value, $key) {
                if ('url' === $key && !empty($value) && $value !== '#') {
                    $value = (string) url($value, [], true, false);
                }
            });
            Cache::set(self::$CACHE_KEY_MENUS_ALL, $menus);
        }
        return $menus;
    }

    /**
     * 查询菜单节点映射
     * @param bool $force
     * @return array
     */
    public static function queryNodeMapping(bool $force = false): array
    {
        if (!$force && Cache::has(self::$CACHE_KEY_MENUS_MAPPING_NODE)) {
            $mapping = Cache::get(self::$CACHE_KEY_MENUS_MAPPING_NODE);
        } else {
            $mapping = SystemMenuModel::column('node', 'id') ?? [];
            $mapping = array_filter($mapping, function ($value) {
                return strlen($value) === 8;
            });
            Cache::set(self::$CACHE_KEY_MENUS_MAPPING_NODE, $mapping);
        }
        return $mapping;
    }

    /**
     * 刷新缓存
     */
    public static function refreshCache()
    {
        self::queryMenus(true);
        self::queryNodeMapping(true);
    }

    /**
     *  清理缓存
     */
    public static function clearCache()
    {
        Cache::delete(self::$CACHE_KEY_MENUS_ALL);
        Cache::delete(self::$CACHE_KEY_MENUS_MAPPING_NODE);
    }

    /**
     * 统一获取菜单
     * @param int|null $roleId
     * @return array
     * @throws JsonException
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     * @throws DbException
     */
    public static function obtainMenus(?int $roleId = null)
    {
        $menus = self::queryMenus();
        if (null !== $roleId) {
            $menuIds = AdminRole::getExtMenu($roleId);
            $menus = self::filterById($menuIds, $menus);
        }
        return $menus;
    }

    /**
     * 菜单树过滤
     * @param array $allowIds
     * @param array $menus
     * @return array
     * @throws JsonException
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     * @throws DbException
     */
    public static function filterById(array $allowIds, array $menus)
    {
        $newMenus = [];
        foreach ($menus as $key => $menu) {
            // 初始化必要数据
            $result = [];
            isset($menu['children']) || $menu['children'] = [];
            // 如果菜单子级不为空则过滤子级
            if (!empty($menu['children'])) {
                $result = self::filterById($allowIds, $menu['children']);
            }
            // 当前菜单是允许访问且（不存在子级或子级不能被过滤掉）
            if (in_array($menu['id'], $allowIds) && (empty($menu['children']) || !empty($result))) {
                $menu['children'] = $result;
                $newMenus[] = $menu;
            }
        }
        return $newMenus;
    }

    /**
     * 导出菜单
     * @return bool
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     * @throws DbException
     */
    public static function export()
    {
        $nodes_dir = App::getRootPath() . 'phinx';
        file_exists($nodes_dir) || mkdir($nodes_dir, 0755, true);
        $datas = SystemMenuModel::select();
        $datas->map(function (SystemMenuModel $menu) {
            empty($menu->create_time) && $menu->create_time = -1;
        });
        try {
            $nodes_data = VarExporter::export($datas->toArray());
        } catch (ExceptionInterface $e) {
            $nodes_data = '[]';
        }
        $date = date('c');
        file_put_contents($nodes_dir . '/menus.php', "<?php\n//export date: {$date}\nreturn {$nodes_data};\n");
        return true;
    }

    /**
     * @param bool        $dryRun
     * @param string|null $message
     * @return bool
     * @throws Exception
     */
    public static function import(bool $dryRun = false, string &$message = null)
    {
        $update_file = App::getRootPath() . 'phinx/menus.php';
        if (file_exists($update_file)) {
            /** @noinspection PhpIncludeInspection */
            $update_data = require $update_file;
            if (!$dryRun) {
                $file_hash = hash('md5', serialize($update_data));
                if (System::getLabel('dep_data_menu_ver') === $file_hash) {
                    $message = '<comment>数据无需更新</comment>';
                    return true;
                }
                $p = new SystemMenuModel();
                try {
                    $p->startTrans();
                    $p->where('id', '>', '0')->delete();
                    $p->insertAll($update_data);
                    self::clearCache();
                    $p->commit();
                } catch (Exception $exception) {
                    $p->rollback();
                    /** @noinspection PhpUnhandledExceptionInspection */
                    throw $exception;
                }
                System::setLabel('dep_data_menu_ver', $file_hash);
            }
            $message = '<info>数据更新完成</info>';
            return true;
        }
        $message = '<error>数据文件不存在</error>';
        return false;
    }
}
