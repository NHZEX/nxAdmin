<?php

namespace app\Logic;

use app\Model\AdminRole as AdminRoleModel;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\facade\Cache;
use Zxin\Think\Auth\Permission;
use function array_flip;
use function array_intersect_key;
use function json_decode;

class AdminRole extends Base
{
    protected static $CACHE_KEY_ROLE_EXT = 'system:role:ext';
    protected static $CACHE_ROLE         = 'system:role';

    /**
     * 刷新缓存
     * @param AdminRoleModel $data
     */
    public static function refreshCache(AdminRoleModel $data)
    {
        self::queryExt($data->id, true);
        self::queryPermission($data->id, true);
        self::queryPermission(-1, true);
    }

    /**
     * 销毁缓存
     * @param AdminRoleModel $data
     */
    public static function destroyCache(AdminRoleModel $data)
    {
        self::destroyCacheById($data->id);
    }

    public static function destroyCacheById(int $rid)
    {
        Cache::delete(self::$CACHE_ROLE . ':' . $rid . ':ext');
        Cache::delete(self::$CACHE_ROLE . ':' . $rid . ':permission');
    }

    /**
     * 查询角色扩展数据
     * @param int  $roleId
     * @param bool $force
     * @return array
     */
    public static function queryExt(int $roleId, bool $force = false): array
    {
        $key = self::$CACHE_ROLE . ':' . $roleId . ':ext';
        if (!$force && Cache::has($key)) {
            $ext = Cache::get($key);
        } else {
            $value = AdminRoleModel::where('id', $roleId)->value('ext', '{}');
            $ext = json_decode($value, true);
            Cache::set($key, $ext);
        }
        return $ext;
    }

    /**
     * @param int  $roleId
     * @param bool $force
     * @return array
     */
    public static function queryPermission(int $roleId, bool $force = false): array
    {
        $key = self::$CACHE_ROLE . ':' . $roleId . ':permission';
        if (!$force && Cache::has($key)) {
            $allowPermission = Cache::get($key);
        } else {
            $permission = Permission::getInstance();
            $allowPermission = $permission->allPermission();
            if (-1 !== $roleId) {
                $ext = self::queryExt($roleId);

                $permission = array_flip($ext['permission'] ?? []);
                $allowPermission = array_intersect_key($permission, $allowPermission);
            }
            Cache::set($key, $allowPermission);
        }
        return $allowPermission;
    }

    /**
     * 获取角色授权权限数据
     * @param int $roleID
     * @return array
     */
    public static function getExtPermission(int $roleID)
    {
        return self::queryExt($roleID)[AdminRoleModel::EXT_PERMISSION] ?? [];
    }

    /**
     * 获取角色授权权限数据
     * @param int $roleID
     * @return array
     */
    public static function getExtAgent(int $roleID)
    {
        return self::queryExt($roleID)[AdminRoleModel::EXT_AGENT] ?? [];
    }

    /**
     * 保存角色权限
     * @param int   $roleID
     * @param array $hashArr
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public static function savePermission(int $roleID, array $hashArr)
    {
        /** @var AdminRoleModel $role */
        $role = AdminRoleModel::find($roleID);
        $role->setJsonData('ext', AdminRoleModel::EXT_PERMISSION, $hashArr);
        $role->save();
        self::refreshCache($role);
    }
}
