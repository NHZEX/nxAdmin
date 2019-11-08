<?php
declare(strict_types=1);

namespace app\Service\Auth\Model;

use think\db\Query;
use think\Model;

/**
 * Class Permission
 * @package app\Service\Auth\Model
 *
 * @property int    $id      节点id
 * @property int    $sort    节点排序
 * @property int    $genre   节点类型
 * @property string $pid     父节点id
 * @property string $name    权限名称
 * @property string $control 授权内容
 * @property string $desc    权限描述
 */
class Permission extends Model
{
    const GENRE_GROUP = 1;
    const GENRE_NODE = 2;
    const GENRE_CUSTOMIZE = 3;

    protected $table = 'permission';

    protected $type = [
        'control' => ['json', JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES],
    ];

    protected $readonly = ['genre'];

    public function scopeGroup(Query $query)
    {
        $query->where('genre', self::GENRE_GROUP);
    }

    public function scopeNode(Query $query)
    {
        $query->where('genre', self::GENRE_NODE);
    }

    public function scopeCustomize(Query $query)
    {
        $query->where('genre', self::GENRE_CUSTOMIZE);
    }

    /**
     * 获取文本树
     * @param array|null $data
     * @param string     $index
     * @param int        $level
     * @return array
     */
    public static function getTextTree(?array $data = null, string $index = '__ROOT__', int $level = 0)
    {
        if (null === $data) {
            $data = (new static())
                ->whereIn('genre', [self::GENRE_GROUP, self::GENRE_CUSTOMIZE])
                ->order(['pid' => 'asc', 'sort' => 'desc'])
                ->column('id,pid,sort,name,desc', 'id');
        }
        $tree = [];
        foreach ($data as $id => $menu) {
            if ($menu['pid'] === $index) {
                $menu['__name'] = str_repeat('   ├  ', $level) . $menu['name'];
                $tree[] = $menu;
                $tree = array_merge($tree, self::getTextTree($data, $menu['name'], $level + 1));
            }
        }

        return $tree;
    }
}
