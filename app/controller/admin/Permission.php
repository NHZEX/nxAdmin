<?php
declare(strict_types=1);

namespace app\controller\admin;

use app\Service\Auth\Annotation\Auth;
use app\Service\Auth\AuthScan;
use app\Service\Auth\Model\Permission as PermissionModel;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\Response;
use think\response\View;

class Permission extends Base
{
    /**
     * @Auth("permission.info")
     * @return View
     */
    public function index()
    {
        return view_current();
    }

    /**
     * @Auth("permission.info")
     * @return View
     */
    public function edit()
    {
        return view_current();
    }

    /**
     * @Auth("permission.info")
     * @return Response
     */
    public function permissionTree()
    {
        return self::showSucceed(PermissionModel::getTextTree(null, '__ROOT__', 1));
    }

    /**
     * @Auth("permission.info")
     * @param $id
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function get($id)
    {
        /** @var PermissionModel $info */
        $info = PermissionModel::find($id);
        if ($info->control && isset($info->control['allow'])) {
            $allow = PermissionModel::whereIn('name', $info->control['allow'])
                ->field(['name', 'desc'])
                ->select();
            $info['allow'] = $allow;
        }
        return self::showSucceed($info);
    }

    /**
     * @Auth("permission.edit")
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function save()
    {
        $input = $this->request->param(['id', 'pid', 'name', 'desc']);
        if (empty($input['id'])) {
            $data = new PermissionModel();
            $input['genre'] = PermissionModel::GENRE_CUSTOMIZE;
        } else {
            $data = PermissionModel::find($input['id']);
            if ($data['genre'] !== PermissionModel::GENRE_CUSTOMIZE) {
                unset($input['pid']);
            }
        }
        $data->save($input);
        return self::showMsg(CODE_SUCCEED);
    }

    /**
     * @Auth("permission.del")
     * @param $id
     * @return Response
     */
    public function del($id)
    {
        PermissionModel::destroy($id);
        return self::showMsg(CODE_SUCCEED);
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

    /**
     * @Auth("permission.lasting")
     * 持久化权限
     * @param \app\Service\Auth\Permission $permission
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function lasting(\app\Service\Auth\Permission $permission)
    {
        $permission->export();
        return self::showSucceed();
    }
}
