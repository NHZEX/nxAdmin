<?php
declare(strict_types=1);

namespace app\Logic;

use function array_unshift;

class SystemLogic
{
    public static function resetPermissionCache(): void
    {
        $roleIds = (new \app\Model\AdminRole())->column('id');
        array_unshift($roleIds, -1);
        foreach ($roleIds as $rid) {
            AdminRole::destroyCacheById($rid);
        }
    }
}
