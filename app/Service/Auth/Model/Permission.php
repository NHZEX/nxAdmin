<?php
declare(strict_types=1);

namespace app\Service\Auth\Model;

use think\Model;

/**
 * Class Permission
 * @package app\Service\Auth\Model
 *
 * @property int    $id      节点id
 * @property int    $sort    节点排序
 * @property string $pid     父节点id
 * @property string $name    权限名称
 * @property string $control 授权内容
 * @property string $desc    权限描述
 */
class Permission extends Model
{
    protected $table = 'permission';

    /**
     * 获取文本树
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
                ->column('id,pid,sort,name,desc', 'id');
        }
        $tree = [];
        foreach ($data as $id => $menu) {
            if ($menu['pid'] === $index) {
                $menu['name'] = str_repeat('   ├  ', $level) . $menu['name'];
                $tree[] = $menu;
                $tree = array_merge($tree, self::getTextTree($data, $id, $level + 1));
            }
        }

        return $tree;
    }
}
