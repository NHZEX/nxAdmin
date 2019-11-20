<?php
declare(strict_types=1);

namespace app\Service\Auth;

use think\facade\Cache;

class Permission
{
    protected static $CACHE_KEY_PERMISSION = 'auth:permission';


    /**
     * 刷新权限缓存
     * @return void
     */
    public static function refresh(): void
    {
        self::all(true);
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
}
