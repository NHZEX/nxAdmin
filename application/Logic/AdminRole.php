<?php
/**
 * Created by PhpStorm.
 * User: NHZEXG
 * Date: 2019/3/7
 * Time: 18:28
 */

namespace app\Logic;

use app\Model\AdminRole as AdminRoleModel;
use think\facade\Cache;

class AdminRole extends Base
{
    protected static $CACHE_KEY_ROLE_EXT = 'system:role:ext';

    /**
     * 刷新缓存
     * @param AdminRoleModel $data
     * @throws \app\Exception\JsonException
     */
    public static function refreshCache(AdminRoleModel $data)
    {
        self::queryExt($data->id, true);
    }

    /**
     * 销毁缓存
     * @param AdminRoleModel $data
     */
    public static function destroyCache(AdminRoleModel $data)
    {
        Cache::rm(self::getRoleExtCacheKey($data->id));
    }

    /**
     * 获取缓存KEY
     * @param int $roleId
     * @return string
     */
    public static function getRoleExtCacheKey(int $roleId)
    {
        return self::$CACHE_KEY_ROLE_EXT . ':' . $roleId;
    }

    /**
     * 查询角色扩展数据
     * @param int  $roleId
     * @param bool $force
     * @return array
     * @throws \app\Exception\JsonException
     */
    public static function queryExt(int $roleId, bool $force = false): array
    {
        $key = self::getRoleExtCacheKey($roleId);
        if (!$force && Cache::has($key)) {
            $ext = Cache::get($key);
        } else {
            $value = AdminRoleModel::wherePk($roleId)->value('ext', '{}');
            $ext = json_decode_throw_on_error($value);
            Cache::set($key, $ext);
        }
        return $ext;
    }

    /**
     * 获取角色授权菜单数据
     * @param int $roleID
     * @return array
     * @throws \app\Exception\JsonException
     */
    public static function getExtMenu(int $roleID): array
    {
        return self::queryExt($roleID)[AdminRoleModel::EXT_MENU] ?? [];
    }

    /**
     * 获取角色授权权限数据
     * @param int $roleID
     * @return array
     * @throws \app\Exception\JsonException
     */
    public static function getExtPermission(int $roleID)
    {
        return self::queryExt($roleID)[AdminRoleModel::EXT_PERMISSION] ?? [];
    }

    /**
     * 保存角色菜单
     * User: Johnson
     * @param       $roleID
     * @param array $menuHashs
     * @throws \app\Exception\JsonException
     */
    public static function saveMenu($roleID, array $menuHashs)
    {
        $role = AdminRoleModel::find($roleID);
        $role->setJsonData('ext', AdminRoleModel::EXT_MENU, $menuHashs);
        $role->save();
        self::refreshCache($role);
    }

    /**
     * 保存角色权限
     * User: Johnson
     * @param int   $roleID
     * @param array $hashArr
     * @throws \app\Exception\JsonException
     */
    public static function savePermission(int $roleID, array $hashArr)
    {
        $role = AdminRoleModel::find($roleID);
        $role->setJsonData('ext', AdminRoleModel::EXT_PERMISSION, $hashArr);
        $role->save();
        self::refreshCache($role);
    }

    /**
     * @param int    $roleId
     * @param string $hash
     * @return bool|int|mixed|string
     * @throws \app\Exception\JsonException
     */
    public static function isPermissionAllowed(int $roleId, string $hash)
    {
        return in_array($hash, self::getExtPermission($roleId));
    }
}
