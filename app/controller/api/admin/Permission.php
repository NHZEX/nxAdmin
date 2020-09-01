<?php

namespace app\controller\api\admin;

use app\Service\Auth\Facade\Auth as AuthFacade;
use think\Response;
use Zxin\Think\Auth\Annotation\Auth;
use Zxin\Think\Auth\AuthScan;
use Zxin\Think\Auth\Permission as AuthPermission;
use function array_merge;
use function func\reply\reply_bad;
use function func\reply\reply_not_found;
use function func\reply\reply_succeed;

class Permission extends Base
{
    /**
     * @Auth("admin.permission.info")
     * @param AuthPermission $permission
     * @return Response
     */
    public function index(AuthPermission $permission)
    {
        $data = $permission->getTree('__ROOT__', 1);

        return reply_succeed($data);
    }

    /**
     * @Auth("admin.permission.info")
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
     * @Auth("admin.permission.edit")
     * @param          $id
     * @param AuthScan $authScan
     * @return Response
     */
    public function update($id, AuthScan $authScan)
    {
        if (!$this->allowAccess()) {
            return reply_bad(CODE_CONV_ACCESS_CONTROL, '无权限执行该操作', null, 403);
        }

        $input = $this->request->only(['sort', 'desc']);

        if (empty($input)) {
            return reply_bad();
        }
        if (!empty($input['sort'])) {
            $input['sort'] = (int) $input['sort'];
        }

        $perm = AuthPermission::getInstance();

        if (!$perm->queryPermission($id)) {
            return reply_not_found();
        }

        $permissions = $perm->getPermission();
        $permissions[$id] = array_merge($permissions[$id], $input);
        $perm->setPermission($permissions);

        $authScan->export($perm->getStorage()->toArray());

        return reply_succeed();
    }

    /**
     * 扫描权限
     * @Auth("admin.permission.scan")
     * @param AuthScan $authScan
     * @return Response
     */
    public function scan(AuthScan $authScan)
    {
        if (!$this->allowAccess()) {
            return reply_bad(CODE_CONV_ACCESS_CONTROL, '无权限执行该操作', null, 403);
        }
        $authScan->refresh();
        return reply_succeed();
    }

    private function allowAccess()
    {
        return $this->app->isDebug() && AuthFacade::check() && AuthFacade::user()->isSuperAdmin();
    }
}
