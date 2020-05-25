<?php
declare(strict_types=1);

namespace app\Service\Auth;

use think\App;

class Permission
{
    /**
     * @var AuthStorage
     */
    protected $storage = null;

    public static function getInstance(): Permission
    {
        return App::getInstance()->make(Permission::class);
    }

    /**
     * 获取树
     * @param array|null $data
     * @param string     $index
     * @param int        $level
     * @return array
     */
    public function getTree(
        string $index = '__ROOT__',
        int $level = 0,
        ?array $data = null
    ) :array {
        if (null === $data) {
            $data = array_merge([], $this->loadStorage()->permission);
            usort($data, function ($a, $b) {
                return $a['sort'] <=> $b['sort'];
            });
        }
        $tree = [];
        foreach ($data as $permission) {
            if ($permission['pid'] === $index) {
                $permission['title'] = $permission['name'];
                $permission['spread'] = true;
                $permission['valid'] = !empty($permission['allow']);
                $permission['children'] = $this->getTree($permission['title'], $level + 1, $data);
                $tree[] = $permission;
            }
        }
        return $tree;
    }

    protected function loadStorage(): ?AuthStorage
    {
        if (empty($this->storage)) {
            $filename = app_path() . 'auth_storage.php';
            /** @noinspection PhpIncludeInspection */
            $this->storage = new AuthStorage(require_once $filename);
        }
        return $this->storage;
    }

    /**
     * @return bool
     */
    public function hasStorage(): bool
    {
        return $this->loadStorage() !== null;
    }

    /**
     * @return AuthStorage
     */
    public function getStorage(): AuthStorage
    {
        return $this->storage;
    }

    /**
     * @return array
     */
    public function getPermission(): array
    {
        return $this->loadStorage()->permission;
    }

    /**
     * @param array $permission
     * @return void
     */
    public function setPermission(array $permission): void
    {
        $this->loadStorage()->permission = $permission;

        return;
    }

    /**
     * 查询节点
     * @param string $node
     * @return array|null
     */
    public function queryFeature(string $node): ?array
    {
        return $this->loadStorage()->features[$node] ?? null;
    }

    /**
     * 查询权限
     * @param string $name
     * @return array|null
     */
    public function queryPermission(string $name): ?array
    {
        return $this->loadStorage()->permission[$name] ?? null;
    }

    public function getPermissionsByFeature($feature): ?array
    {
        return $this->loadStorage()->fe2pe[$feature] ?? null;
    }

    public function allPermission()
    {
        return $this->loadStorage()->pe2fe;
    }

    public function contain(string $node)
    {
        $storage = $this->loadStorage();
        return isset($storage->pe2fe[$node]) || isset($storage->fe2pe[$node]);
    }
}
