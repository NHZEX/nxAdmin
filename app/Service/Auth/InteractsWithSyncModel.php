<?php
declare(strict_types=1);

namespace app\Service\Auth;

use app\Service\Auth\Model\Permission;
use RuntimeException;
use think\Collection;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use function array_pop;
use function count;
use function explode;
use function implode;
use function ksort;

/**
 * Trait InteractsWithSyncModel
 * @package app\Service\Auth
 * @property \app\Service\Auth\Permission $permission
 */
trait InteractsWithSyncModel
{
    private $increase = 1;

    /**
     * 刷新权限到数据库
     */
    public function refresh()
    {
        $this->scanAuthAnnotation();

        $groupControl = function (array $control) {
            // 处理control
            $control_array = [];
            foreach ($control as $key => $x) {
                $control_array[$key] = 'node@' . $x;
            }
            return ['allow' => array_values($control_array)];
        };

        Permission::transaction(function () use ($groupControl) {
            $data = array_merge(
                $this->fillPermission('group', $this->getPermissions(), $groupControl),
                $this->fillPermission('node', $this->getNodes()),
                $this->fillCustomize()
            );
            $data = array_values($data);
            dump($this->fillCustomize());

            foreach ($data as &$item) {
                $item['id'] = $this->increase++;
            }

            Permission::where('id', '>', '0')->delete();
            Permission::insertAll($data);
        });

        $this->permission->refresh();
    }

    /**
     * @return array
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    protected function fillCustomize()
    {
        $result = Permission::scope('customize')->hidden(['id'])->select();
        $result = array_map(function ($v) {
            return [
                'sort' => $v['sort'],
                'genre' => $v['genre'],
                'pid' => $v['pid'],
                'name' => $v['name'],
                'control' => json_encode($v['control'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'desc' => $v['desc'],
            ];
        }, $result->toArray());

        return $result;
    }

    /**
     * @param string        $scope
     * @param array         $data
     * @param callable|null $handleControl
     * @return array|false|Collection
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    protected function fillPermission(string $scope, array $data, ?callable $handleControl = null)
    {
        $genre = [
            'group' => Permission::GENRE_GROUP,
            'node' => Permission::GENRE_NODE,
        ];
        $delimiter = [
            'group' => '.',
            'node' => '/',
        ];
        $original = Permission::scope($scope)->hidden(['id'])->select();
        $original = array_combine($original->column('name'), $original->toArray());

        $result = [];
        foreach ($data as $permission => $control) {
            // 填充父节点
            $pid = $this->fillParent($result, $original, $permission, $genre[$scope], $delimiter[$scope]);
            // 处理control
            $control = $handleControl ? $handleControl($control) : $control;
            // 生成插入数据
            if (isset($original[$permission]) && !empty($original[$permission]['control'])) {
                $sort = $original[$permission]['sort'];
                $desc = $original[$permission]['desc'];
            } else {
                $sort = 0;
                $desc = '';
            }
            $result[$permission] = [
                'sort' => $sort,
                'genre' => $genre[$scope],
                'pid' => $pid,
                'name' => $permission,
                'control' => json_encode($control, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'desc' => $desc,
            ];
        }

        ksort($result);
        return $result;
    }

    /**
     * 填充父节点
     * @param array  $data
     * @param array  $original
     * @param string $permission
     * @param int    $genre
     * @param string $delimiter
     * @return string
     */
    protected function fillParent(array &$data, array $original, string $permission, int $genre, string $delimiter)
    {
        $parents = explode($delimiter, $permission) ?: [];
        if (1 === count($parents)) {
            return self::ROOT_NODE;
        }
        array_pop($parents);
        $result = implode($delimiter, $parents);

        while (count($parents)) {
            $curr = implode($delimiter, $parents);
            array_pop($parents);
            $parent = implode($delimiter, $parents) ?: self::ROOT_NODE;

            if (isset($original[$curr])) {
                if (!empty($original[$curr]['control'])) {
                    throw new RuntimeException('父节点不允许分配权限: ' . $curr);
                }
                $sort = $original[$curr]['sort'];
                $desc = $original[$curr]['desc'];
            } else {
                $sort = 0;
                $desc = '';
            }
            $data[$curr] = [
                'sort' => $sort,
                'genre' => $genre,
                'pid' => $parent,
                'name' => $curr,
                'control' => null,
                'desc' => $desc,
            ];
        }

        return $result;
    }
}
