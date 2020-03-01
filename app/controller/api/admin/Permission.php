<?php

namespace app\controller\api\admin;

use app\Service\Auth\Annotation\Auth;
use app\Service\Auth\AuthScan;
use app\Service\Auth\Permission as AuthPermission;
use think\Response;

class Permission extends Base
{
    /**
     * @Auth("permission.info")
     * @param AuthPermission $permission
     * @return Response
     */
    public function index(AuthPermission $permission)
    {
        $data = $permission->getTree('__ROOT__', 1);

        return self::showJson($data);
    }

    /**
     * @Auth("permission.info")
     * @param                $id
     * @param AuthPermission $permission
     * @return Response
     */
    public function read($id, AuthPermission $permission)
    {
        if (($info = $permission->queryPermission($id)) === null) {
            return self::showCode(404);
        }

        $allow = [];
        foreach ($info['allow'] ?? [] as $index => $item) {
            $feature = $permission->queryFeature($item);
            if ($feature) {
                $allow[] =  [
                    'name' => $item,
                    'desc' => $feature['desc'],
                ];
            }
        }
        $info['allow'] = $allow;

        return self::showJson($info);
    }

    /**
     * 扫描权限
     * @Auth("permission.scan")
     * @param AuthScan $authScan
     * @return Response
     */
    public function scan(AuthScan $authScan)
    {
        $authScan->refresh();
        return self::showSucceed();
    }
}
