<?php

namespace app\controller\api\admin;

use app\Model\AdminUser;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\Response;
use Util\Reply;
use Zxin\Think\Auth\Annotation\Auth;
use Zxin\Think\Auth\Annotation\AuthNode;
use Zxin\Think\Validate\Annotation\Validation;
use function trim;

/**
 * Class User
 * @package app\controller\api\admin
 */
class User extends Base
{
    /**
     * @Auth("admin.user.info")
     * @param int $limit
     * @return Response
     * @throws DbException
     */
    public function index(int $limit = 1)
    {
        $where = $this->buildWhere($this->request->param(), [
            ['genre', '='],
            ['role_id', '='],
        ]);
        $result = (new AdminUser())
            ->where($where)
            ->with(['beRoleName'])
            ->append(['status_desc', 'genre_desc', 'avatar_data'])
            ->paginate($limit);

        return Reply::table($result);
    }

    /**
     * @Auth("admin.user.info")
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
            return Reply::notFound();
        }
        return Reply::success($result);
    }

    /**
     * @Auth("admin.user.add")
     * @AuthNode("创建系统用户")
     * @Validation("@Admin.User", scene="_")
     * @return Response
     */
    public function save()
    {
        AdminUser::create($this->getFilterInput());

        return Reply::create();
    }

    /**
     * @Auth("admin.user.edit")
     * @AuthNode("更改系统用户")
     * @Validation("@Admin.User", scene="_")
     * @param int $id
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function update(int $id)
    {
        /** @var AdminUser $result */
        $result = AdminUser::find($id);
        if (empty($result)) {
            return Reply::notFound();
        }

        $data = $this->getFilterInput();
        if (isset($data['password']) && empty(trim($data['password']))) {
            unset($data['password']);
        }
        $result->save($data);

        return Reply::success();
    }

    /**
     * @Auth("admin.user.del")
     * @AuthNode("删除系统用户")
     * @param int $id
     * @return Response
     */
    public function delete(int $id)
    {
        AdminUser::destroy($id);

        return Reply::success();
    }
}
