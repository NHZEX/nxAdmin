<?php

namespace app\controller\api\admin;

use app\Model\AdminUser;
use app\Service\Auth\Annotation\Auth;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\Response;

/**
 * Class Users
 * @package app\controller\api\admin
 */
class Users extends Base
{
    /**
     * @Auth("user.info")
     * @param int $limit
     * @return Response
     * @throws DbException
     */
    public function index(int $limit = 1)
    {
        // todo 数据访问限制
        $result = (new AdminUser())
            ->db()
            ->with(['beRoleName'])
            ->append(['status_desc', 'genre_desc', 'avatar_data'])
            ->paginate($limit);

        return self::showTable($result);
    }

    /**
     * @Auth("user.info")
     * @param int $id
     * @return Response
     * @throws DbException
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     */
    public function read(int $id)
    {
        $result = AdminUser::find($id);
        if (empty($result)) {
            return self::showCode(404);
        }
        return self::showJson($result);
    }

    /**
     * @Auth("user.edit")
     * @return Response
     */
    public function save()
    {
        AdminUser::create($this->request->param());

        return self::showCode(204);
    }

    /**
     * @Auth("user.del")
     * @param int $id
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function update(int $id)
    {
        $data = $this->request->param();
        if (isset($data['password']) && empty(trim($data['password']))) {
            unset($data['password']);
        }
        /** @var AdminUser $result */
        $result = AdminUser::find($id);
        if (empty($result)) {
            return self::showCode(404);
        }
        $result->save($this->request->param());

        return self::showSucceed();
    }

    /**
     * @param int $id
     * @return Response
     */
    public function delete(int $id)
    {
        AdminUser::destroy($id);

        return self::showSucceed();
    }
}
