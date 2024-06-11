<?php

declare(strict_types=1);

namespace app\Model\Admin;

use app\Model\Base;
use app\Service\Transaction\MainTrans;
use function Zxin\Arr\array_group;

/**
 * Model: Table of user_role_relation.
 *
 * @property int $id
 * @property int $user_id
 * @property int $role_id
 * @property int $create_time
 */
final class UserRoleRelationModel extends Base
{
    public $table = 'user_role_relation';
    public $pk = 'id';

    public static function setUserRoles(int $userId, array $roleIds): void
    {
        MainTrans::callback(function () use ($userId, $roleIds) {
            $roleIds = \array_unique($roleIds);
            $roleIds = \array_filter($roleIds, fn($roleId) => $roleId > 0);

            $userRolesId = (new UserRoleRelationModel())
                ->lock(true)
                ->where('user_id', '=', $userId)
                ->column('role_id');

            $addRoleIds = array_diff($roleIds, $userRolesId);
            $delRoleIds = array_diff($userRolesId, $roleIds);

            if ($addRoleIds) {
                $createTime = \time();

                $addData = array_map(fn($roleId) => [
                    'user_id' => $userId,
                    'role_id' => $roleId,
                    'create_time' => $createTime
                ], $addRoleIds);

                (new UserRoleRelationModel())->insertAll($addData);
            }

            if ($delRoleIds) {
                (new UserRoleRelationModel())
                    ->where('user_id', '=', $userId)
                    ->whereIn('role_id', $delRoleIds)
                    ->delete();
            }
        });
    }

    public static function getUserRoles(int $userId): array
    {
        return (new UserRoleRelationModel())
            ->where('user_id', '=', $userId)
            ->column('role_id');
    }

    public static function getUserRolesPermission(int $userId, bool $force = false): array
    {
        $permissionGroup = [];
        foreach (UserRoleRelationModel::getUserRoles($userId) as $roleId) {
            $permissionGroup[] = \app\Logic\AdminRole::queryPermission($roleId, $force);
        }

        $permission = array_merge(...$permissionGroup);

        return $permission;
    }

    public static function listUserRolesId(array $userIds): array
    {
        $data = (new UserRoleRelationModel())
            ->whereIn('user_id', $userIds)
            ->column('role_id', 'user_id');

        return array_group($data, 'user_id');
    }

    public static function listUserRolesIdAndName(array $userIds): array
    {
        $data = (new UserRoleRelationModel())
            ->alias('ur')
            ->whereIn('ur.user_id', $userIds)
            ->join('admin_role r', 'r.id = ur.role_id')
            ->column(['r.id', 'r.name', 'ur.user_id']);

        return array_group($data, 'user_id', false);
    }
}
