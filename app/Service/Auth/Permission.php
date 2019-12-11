<?php
declare(strict_types=1);

namespace app\Service\Auth;

use app\Model\System;
use Exception;
use Symfony\Component\VarExporter\Exception\ExceptionInterface;
use Symfony\Component\VarExporter\VarExporter;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\facade\Cache;

class Permission
{
    protected static $CACHE_KEY_PERMISSION = 'auth:permission';
    protected static $CACHE_KEY_NODE_MAPPING = 'auth:node-mapping';

    /**
     * 刷新权限缓存
     * @return void
     */
    public function refresh(): void
    {
        self::all(true);
        self::nodes(true);
    }

    /**
     * 全部节点
     * @param bool $force
     * @return array
     */
    public function allNode(bool $force = false): array
    {
        return array_filter($this->all($force), function ($control, $name) {
            return null !== $control && 'node@' === substr($name, 0, 5);
        }, ARRAY_FILTER_USE_BOTH);
    }

    /**
     * 查询节点
     * @param string $node
     * @return array|null
     */
    public function queryNode(string $node): ?array
    {
        return $this->all()['node@' . $node] ?? null;
    }

    /**
     * 全部权限
     * @param bool $force
     * @return array
     */
    public function all(bool $force = false): array
    {
        if (!$force && Cache::has(self::$CACHE_KEY_PERMISSION)) {
            $data = Cache::get(self::$CACHE_KEY_PERMISSION, []);
        } else {
            $data = (new Model\Permission())
                ->field(['name', 'control'])
                ->column('control', 'name');
            $data = array_map(function ($control) {
                return $control ? (json_decode($control, true) ?: null) : null;
            }, $data);
            Cache::set(self::$CACHE_KEY_PERMISSION, $data);
        }
        return $data;
    }

    public function nodes(bool $force = false): array
    {
        if (!$force && Cache::has(self::$CACHE_KEY_NODE_MAPPING)) {
            $data = Cache::get(self::$CACHE_KEY_NODE_MAPPING, []);
        } else {
            $data = [];
            $i = 0;
            foreach ($this->all() as $permission => $control) {
                if (null !== $control) {
                    foreach ($control['allow'] ?? [] as $node) {
                        $data[$node][$permission] = $i++;
                    }
                }
            }
            Cache::set(self::$CACHE_KEY_NODE_MAPPING, $data);
        }
        return $data;
    }

    /**
     * 导出所有权限
     * @return bool
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function export()
    {
        $nodes_dir = root_path('phinx');
        file_exists($nodes_dir) || mkdir($nodes_dir, 0755, true);
        $outdata = [];
        foreach (Model\Permission::select() as $permission) {
            /** @var Model\Permission $permission */
            $outdata[] = $permission->getOrigin();
        }
        try {
            $nodes_data = VarExporter::export($outdata);
        } catch (ExceptionInterface $e) {
            $nodes_data = '[]';
        }
        $date = date('c');
        $contents = "<?php\n//export date: {$date}\nreturn {$nodes_data};\n";
        file_put_contents($nodes_dir . '/nodes.php', $contents);
        return true;
    }

    public function import(bool $dryRun = false, string &$message = null): bool
    {
        $update_file = root_path('phinx') . 'nodes.php';
        if (file_exists($update_file)) {
            /** @noinspection PhpIncludeInspection */
            $update_data = require $update_file;
            $file_hash = hash('md5', serialize($update_data));
            if (!$dryRun) {
                if (System::getLabel('dep_data_nodes_ver') === $file_hash) {
                    $message = '<comment>数据无需更新</comment>';
                    return true;
                }
                $p = new Model\Permission();
                try {
                    $p->startTrans();
                    $p->where('id', '>', '0')->delete();
                    $p->insertAll($update_data);
                    $this->refresh();
                    $p->commit();
                } catch (Exception $exception) {
                    $p->rollback();
                    /** @noinspection PhpUnhandledExceptionInspection */
                    throw $exception;
                }
                System::setLabel('dep_data_nodes_ver', $file_hash);
            }
            $message = '<info>数据更新完成</info>';
            return true;
        }
        $message = '<error>数据文件不存在</error>';
        return false;
    }
}
