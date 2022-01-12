<?php

namespace app\Logic;

use function array_unshift;
use function log_debug;

class SystemLogic
{
    public function resetPermissionCache()
    {
        $roleIds = (new \app\Model\AdminRole())->column('id');
        array_unshift($roleIds, -1);
        foreach ($roleIds as $rid) {
            log_debug($rid);

            AdminRole::destroyCacheById($rid);
        }
    }
}
