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
 * Class Role
 * @package app\controller\api\admin
 */
class Role extends Base
{
    /**
     * @Auth("admin.role.info")
     * @param int $limit
     * @return Response
     * @throws DbException
     */
    public function index(int $limit = 1)
    {
        $where = $this->buildWhere($this->request->param(), [
            ['genre', '='],
        ]);
        // todo 数据访问限制
        $result = (new AdminRole())
            ->where($where)
            ->append(['genre_desc', 'status_desc'])
            ->paginate($limit);

        return reply_table($result);
    }

    /**
     * @Auth("admin.role.info")
     * @return Response
     */
    public function select()
    {
        return reply_succeed(AdminRole::buildOption());
    }

    /**
     * @Auth("admin.role.info")
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
     * @Auth("admin.role.add")
     * @return Response
     */
    public function save()
    {
        AdminRole::create($this->getFilterInput());
        return reply_create();
    }

    /**
     * @Auth("admin.role.edit")
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
     * @Auth("admin.role.del")
     * @param $id
     * @return Response
     */
    public function delete($id)
    {
        AdminRole::destroy($id);
        return reply_succeed();
    }
}
