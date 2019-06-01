<?php
/**
 * Created by Automatic build
 * User: Auooru
 * Date: 2019/02/23
 * Time: 10:36
 */

namespace app\Model;

use app\Logic\SystemMenu as SystemMenuLogic;
use think\Model;

/**
 * 系统菜单
 * Class SystemMenu
 * @package app\model
 *
 * @property int $id 主键
 * @property int $pid 父关联
 * @property mixed $sort 菜单排序
 * @property int $status 菜单状态
 * @property string $node 节点
 * @property string $title 菜单标题
 * @property string $icon 菜单图标
 * @property string $url 菜单地址
 * @property int $lock_version 锁版本
 * @property int $create_time 创建时间
 * @property int $update_time 更新时间
 * @property int $delete_time 删除时间
 */
class SystemMenu extends Base
{
    protected $name = 'system_menu';
    protected $pk = 'id';

    const STATUS_NORMAL = 0;
    const STATUS_DISABLE = 1;
    const STATUS_DICT = [
        self::STATUS_DISABLE => '禁用',
        self::STATUS_NORMAL => '正常',
    ];

    public static function onAfterInsert(Model $model)
    {
        SystemMenuLogic::refreshCache();
    }

    public static function onAfterUpdate(Model $model)
    {
        SystemMenuLogic::refreshCache();
    }

    public static function onAfterDelete(Model $model)
    {
        SystemMenuLogic::refreshCache();
    }

    /**
     * 获取状态翻译
     * @return string
     */
    public function getStatusDescAttr()
    {
        return self::STATUS_DICT[$this->getData('status')] ?? '未知';
    }

    /**
     * 获取菜单数组树
     * @param array|null $data
     * @param int        $index
     * @param string     $pkey
     * @return array
     */
    public static function getArrayTree(?array $data = null, int $index = 0, $pkey = 'R') :array
    {
        if (null === $data) {
            $data = (new static())
                ->order(['pid' => 'asc', 'sort' => 'desc'])
                ->column('id,pid,node,status,title,icon,url', 'id');
        }

        $tree = [];
        foreach ($data as $id => $menu) {
            if ($menu['pid'] === $index) {
                $pkey .= "-{$id}";
                $menu['pkey'] = $pkey;
                $tree[] = $menu + [
                    'children' => self::getArrayTree($data, $id, $pkey),
                ];
            }
        }
        return $tree;
    }

    /**
     * 获取菜单文本树
     * @param array|null $data
     * @param int        $index
     * @param int        $level
     * @return array
     */
    public static function getTextTree(?array $data = null, int $index = 0, int $level = 0)
    {
        if (null === $data) {
            $data = (new static())
                ->order(['pid' => 'asc', 'sort' => 'desc'])
                ->column('id,pid,title,status', 'id');
        }
        $level++;
        $tree = [];
        foreach ($data as $id => $menu) {
            if ($menu['pid'] === $index) {
                $tree[$id] = $menu;
                $tree[$id]['title'] = str_repeat('&nbsp;&nbsp;&nbsp;├&nbsp;&nbsp;', $level) . $menu['title'];
                $tree += self::getTextTree($data, $id, $level);
            }
        }

        return $tree;
    }
}
