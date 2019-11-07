<?php
declare(strict_types=1);

namespace app\controller\admin;

use app\Service\Auth\ControllerScan;
use app\Service\Auth\Model\Permission as PermissionModel;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\Response;

class Permission2 extends Base
{
    public function index()
    {
        return view_current();
    }


    public function edit(ControllerScan $scan)
    {
        return view_current([
            'node_tree' => tree_to_table($scan->nodeTree()),
        ]);
    }

    public function permissionTree()
    {
        return self::showSucceed(PermissionModel::getTextTree(null, '__ROOT__', 1));
    }

    /**
     * @param $id
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function get($id)
    {
        return self::showSucceed(PermissionModel::find($id));
    }

    /**
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function save()
    {
        $input = $this->request->param();
        if (empty($input['id'])) {
            $data = new PermissionModel();
        } else {
            $data = PermissionModel::find($input['id']);
        }
        $data->save($input);
        return self::showMsg(CODE_SUCCEED);
    }

    public function del($id)
    {
        PermissionModel::destroy($id);
        return self::showMsg(CODE_SUCCEED);
    }
}
