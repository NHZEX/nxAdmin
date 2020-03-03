<?php

namespace app\controller\api\admin;

use app\Logic\AdminRole;
use app\Service\Auth\Facade\Auth as AuthFacade;
use think\Response;

class Auth extends Base
{
    /**
     * 获取用户信息
     * @\app\Service\Auth\Annotation\Auth()
     * @return Response
     */
    public function userInfo()
    {
        $user = AuthFacade::user();
        $role_id = $user->isSuperAdmin() ? -1 : $user->role_id;
        return self::showSucceed([
            'user' => $user,
            'permission' => AdminRole::queryOnlyPermission($role_id),
        ]);
    }

}
