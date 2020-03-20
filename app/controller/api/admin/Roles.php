<?php

namespace app\controller\api\admin;

use app\Model\AdminRole;
use app\Service\Auth\Annotation\Auth;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\Response;
use function func\reply\reply_create;
use function func\reply\reply_not_found;
use function func\reply\reply_succeed;
use function func\reply\reply_table;

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

        return reply_table($result);
    }

    /**
     * @Auth("role.info")
     * @return Response
     */
    public function select()
    {
        return reply_succeed(AdminRole::buildOption());
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
            return reply_not_found();
        }
        return reply_succeed($result);
    }

    /**
     * @Auth("role.edit")
     * @return Response
     */
    public function save()
    {
        AdminRole::create($this->getFilterInput());
        return reply_create();
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
            return reply_not_found();
        }
        $data->save($this->getFilterInput());
        return reply_succeed();
    }

    /**
     * @Auth("role.del")
     * @param $id
     * @return Response
     */
    public function delete($id)
    {
        AdminRole::destroy($id);
        return reply_succeed();
    }
}
