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
 * @property array  $control 授权内容
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
     * @param array $genre
     * @return array
     */
    public static function sortPermission(array $genre)
    {
        return (new static())
            ->whereIn('genre', $genre)
            ->order(['genre' => 'asc', 'pid' => 'asc', 'sort' => 'desc'])
            ->column('id,pid,sort,name,desc,control', 'id');
    }

    /**
     * 获取树
     * @param array|null $data
     * @param string     $index
     * @param int        $level
     * @param array      $genre
     * @return array
     */
    public static function getTree(
        ?array $data = null,
        string $index = '__ROOT__',
        int $level = 0,
        array $genre = [self::GENRE_GROUP, self::GENRE_CUSTOMIZE]
    ) :array {
        if (null === $data) {
            $data = self::sortPermission($genre);
        }
        $tree = [];
        foreach ($data as $id => $permission) {
            if ($permission['pid'] === $index) {
                $permission['id'] = $permission['name'];
                $permission['title'] = $permission['name'];
                $permission['spread'] = true;
                $permission['valid'] = !empty($permission['control']);
                unset($permission['control']);
                unset($permission['name']);
                $permission['children'] = self::getTree($data, $permission['id'], $level + 1);
                $tree[] = $permission;
            }
        }
        return $tree;
    }

    /**
     * 获取文本树
     * @param array|null $data
     * @param string     $index
     * @param int        $level
     * @param array      $genre
     * @return array
     */
    public static function getTextTree(
        ?array $data = null,
        string $index = '__ROOT__',
        int $level = 0,
        array $genre = [self::GENRE_GROUP, self::GENRE_CUSTOMIZE]
    ) :array {
        if (null === $data) {
            $data = self::sortPermission($genre);
        }
        $tree = [];
        foreach ($data as $id => $permission) {
            if ($permission['pid'] === $index) {
                $permission['__name'] = str_repeat('   ├  ', $level) . $permission['name'];
                $permission['valid'] = !empty($permission['control']);
                unset($permission['control']);
                $tree[] = $permission;
                $tree = array_merge($tree, self::getTextTree($data, $permission['name'], $level + 1));
            }
        }

        return $tree;
    }
}
