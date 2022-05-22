<?php

namespace app\Controller\admin;

use app\Model\AdminUser;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\Response;
use Util\Reply;
use Zxin\Think\Auth\Annotation\Auth;
use Zxin\Think\Auth\Annotation\AuthMeta;
use Zxin\Think\Validate\Annotation\Validation;
use function trim;

/**
 * Class User
 * @package app\Controller\admin
 */
class User extends Base
{
    /**
     * @param int $limit
     * @return Response
     * @throws DbException
     */
    #[Auth("admin.user.info")]
    #[AuthMeta("获取用户信息")]
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
     * @param int $id
     * @return Response
     * @throws DbException
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     */
    #[Auth("admin.user.info")]
    #[AuthMeta("获取用户信息")]
    public function read(int $id)
    {
        $result = AdminUser::find($id);
        if (empty($result)) {
            return Reply::notFound();
        }
        return Reply::success($result);
    }

    /**
     * @return Response
     */
    #[Auth("admin.user.add")]
    #[AuthMeta("添加用户信息")]
    #[Validation(name: "@Admin.User", scene: "_")]
    public function save()
    {
        AdminUser::create($this->getFilterInput());

        return Reply::create();
    }

    /**
     * @param int $id
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    #[Auth("admin.user.edit")]
    #[AuthMeta("更改用户信息")]
    #[Validation(name: "@Admin.User", scene: "_")]
    public function update(int $id)
    {
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
     * @param int $id
     * @return Response
     */
    #[Auth("admin.user.del")]
    #[AuthMeta("删除用户信息")]
    public function delete(int $id)
    {
        AdminUser::destroy($id);

        return Reply::success();
    }
}
