<?php
/**
 * Created by PhpStorm.
 * User: NHZEXG
 * Date: 2019/3/7
 * Time: 18:28
 */

namespace app\Logic;

use app\Model\AdminRole as AdminRoleModel;
use app\Service\Auth\Permission;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\facade\Cache;
use Tp\Db\Query as Query2;

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
        Cache::delete(self::$CACHE_ROLE . ':' . $data->id . ':ext');
        Cache::delete(self::$CACHE_ROLE . ':' . $data->id . ':permission');
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
            $value = AdminRoleModel::wherePk($roleId)->value('ext', '{}');
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
     * @param int  $roleId
     * @param bool $force
     * @return array
     */
    public static function queryOnlyPermission(int $roleId, bool $force = false): array
    {
        $data = [];
        foreach (self::queryPermission($roleId, $force) as $key => $v) {
            $data[$key] = null;
        }
        return $data;
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
     * 保存角色权限
     * @param int   $roleID
     * @param array $hashArr
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public static function savePermission(int $roleID, array $hashArr)
    {
        /** @var AdminRoleModel|Query2 $role */
        $role = AdminRoleModel::find($roleID);
        $role->setJsonData('ext', AdminRoleModel::EXT_PERMISSION, $hashArr);
        $role->save();
        self::refreshCache($role);
    }
}
