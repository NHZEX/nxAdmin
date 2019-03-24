<?php
/**
 * Created by PhpStorm.
 * User: Johnson
 * Date: 2019/1/16
 * Time: 17:26
 */

namespace app\logic;

use app\model\Permission as PermissionModel;
use basis\Util;
use struct\PermissionNode;
use think\facade\App;
use think\facade\Cache;

/**
 * Class Permission
 * @package app\logic
 */
class Permission
{
    protected static $CACHE_KEY_ALL_NODE_FLAGS = 'system:permission:node-flags:all';

    /**
     * 刷新权限缓存
     */
    public static function refreshCache()
    {
        self::queryNodeHashFlagsAll(true);
    }

    /**
     * 查询全部节点的Hash与标识表
     * @param bool $force
     * @return array
     */
    public static function queryNodeHashFlagsAll(bool $force = false): array
    {
        if (!$force && Cache::has(self::$CACHE_KEY_ALL_NODE_FLAGS)) {
            $data = Cache::get(self::$CACHE_KEY_ALL_NODE_FLAGS, []);
        } else {
            $data = (new PermissionModel())
                ->field(['hash', 'flags'])
                ->column('flags', 'hash');
            Cache::set(self::$CACHE_KEY_ALL_NODE_FLAGS, $data);
        }
        return $data;
    }

    /**
     * 由节点Hash获取节点flag标识
     * @param string $hash
     * @return int
     */
    public static function getFlagByHash(string $hash): int
    {
        $nodes = self::queryNodeHashFlagsAll();
        return (int) ($nodes[$hash] ?? 0);
    }

    /**
     * 获取节点中属于菜单的项目
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function queryNodeFlagsIsMenu()
    {
        /** @var PermissionModel[] $nodes */
        $nodes = (new PermissionModel())
            ->field(['id', 'pid', 'action', 'hash', 'class_name'])
            ->whereRaw('flags&'.PermissionModel::FLAG_MENU.'='.PermissionModel::FLAG_MENU)
            ->where('genre', PermissionModel::GENRE_ACTION)
            ->select();

        $count = 1;
        $result = [];
        foreach ($nodes as $node) {
            $rawPath = Util::toSnakeCase($node->class_name) . (empty($node->action) ? '' : '-' . $node->action);
            $result[] = [
                'id' => $node->hash,
                'name' => str_replace(['app\\controller\\', '\\', '-'], ['', '.', '/'], $rawPath, $count),
            ];
        }
        return $result;
    }

    /**
     * 计算节点信息
     * User: Johnson
     * @param string $class_name
     * @param string $action
     * @return PermissionNode
     */
    public static function computeNode(string $class_name, string $action)
    {
        if (!empty($action)) {
            $action = "-{$action}";
        }

        $nkey = strtolower(str_replace("\\", '.', $class_name) . $action);
        $hash = hash('fnv1a32', $nkey);

        return new PermissionNode([
            'nkey' => $nkey,
            'hash' => $hash,
        ]);
    }

    /**
     * 导出权限节点树
     * @return bool
     */
    public static function exportNodes()
    {
        $nodes_dir = App::getRootPath() . 'phinx';
        file_exists($nodes_dir) || mkdir($nodes_dir, 0755, true);
        $nodes_data = var_export(PermissionModel::select()->toArray(), true);
        $date = date('c');
        file_put_contents($nodes_dir . '/nodes.php', "<?php\n//export date: {$date}\nreturn {$nodes_data};");
        return true;
    }

    /**
     * @param bool $dryRun
     * @return bool
     * @throws \think\exception\PDOException
     */
    public static function importNodes(bool $dryRun = false)
    {
        $nodes_file = App::getRootPath() . 'phinx/nodes.php';
        if (file_exists($nodes_file)) {
            /** @noinspection PhpIncludeInspection */
            $nodes_data = require $nodes_file;
            $p = new PermissionModel();
            if (!$dryRun) {
                try {
                    $p->startTrans();
                    $p->where('id', '>', '0')->delete();
                    $p->insertAll($nodes_data);
                    self::refreshCache();
                    $p->commit();
                } catch (\Exception $exception) {
                    $p->rollback();
                    /** @noinspection PhpUnhandledExceptionInspection */
                    throw $exception;
                }
            }
            return true;
        }
        return false;
    }
}
