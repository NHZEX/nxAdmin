<?php
declare(strict_types=1);

namespace app\Service\Auth;

use think\App;

class Permission
{
    public static function getInstance(): Permission
    {
        return App::getInstance()->make(Permission::class);
    }

    /**
     * @var AuthStorage
     */
    protected $storage = null;

    protected function loadStorage(): AuthStorage
    {
        if (empty($this->storage)) {
            $filename = app_path() . 'auth_storage.php';
            /** @noinspection PhpIncludeInspection */
            $this->storage = new AuthStorage(require_once $filename);
        }
        return $this->storage;
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

    public function getPermissionByFeature($feature): ?string
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
