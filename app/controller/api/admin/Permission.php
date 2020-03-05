<?php

namespace app\controller\api\admin;

use app\Service\Auth\Annotation\Auth;
use app\Service\Auth\AuthScan;
use app\Service\Auth\Permission as AuthPermission;
use think\Response;
use function func\reply\reply_bad;
use function func\reply\reply_succeed;

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

        return reply_succeed($data);
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
            return reply_bad();
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

        return reply_succeed($info);
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
        return reply_succeed();
    }
}
