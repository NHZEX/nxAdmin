<?php

namespace app\controller\api\admin;

use app\Model\AdminRole;
use app\Service\Auth\Annotation\Auth;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\Response;

/**
 * Class Roles
 * @package app\controller\api\admin
 */
class Roles extends Base
{
    /**
     * @Auth("role.info")
     * @param int $limit
     * @return Response
     * @throws DbException
     */
    public function index(int $limit = 1)
    {
        // todo 数据访问限制
        $result = (new AdminRole())->db()->append(['genre_desc', 'status_desc'])->paginate($limit);

        return self::showTable($result);
    }

    /**
     * @Auth("role.info")
     * @return Response
     */
    public function select()
    {
        return self::showJson(AdminRole::selectOption());
    }

    /**
     * @Auth("role.info")
     * @param int $id
     * @return Response
     * @throws DbException
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     */
    public function read(int $id)
    {
        $result = AdminRole::find($id);
        if (empty($result)) {
            return self::showCode(404);
        }
        return self::showJson($result);
    }

    /**
     * @Auth("role.edit")
     * @return Response
     */
    public function save()
    {
        AdminRole::create($this->request->param() + ['ext' => '{}'], ['genre', 'name', 'status', 'ext']);
        return self::showCode(201);
    }

    /**
     * @Auth("role.edit")
     * @param $id
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function update($id)
    {
        /** @var AdminRole $data */
        $data = AdminRole::find($id);
        if (empty($data)) {
            return self::showCode(404);
        }
        $data->save($this->request->param());
        return self::showCode(200);
    }

    /**
     * @Auth("role.del")
     * @param $id
     * @return Response
     */
    public function delete($id)
    {
        AdminRole::destroy($id);
        return self::showCode(200);
    }
}
