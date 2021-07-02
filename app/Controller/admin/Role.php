<?php

namespace app\Controller\admin;

use app\Model\AdminRole;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\db\Query;
use think\Response;
use Util\Reply;
use Zxin\Think\Auth\Annotation\Auth;
use Zxin\Think\Auth\Annotation\AuthMeta;
use Zxin\Think\Validate\Annotation\Validation;

/**
 * Class Role
 * @package app\Controller\admin
 */
class Role extends Base
{
    /**
     * @Auth("admin.role.info")
     * @AuthMeta("获取角色信息")
     * @param int $limit
     * @return Response
     * @throws DbException
     */
    public function index(int $limit = 1): Response
    {
        $where = $this->buildWhere($this->request->param(), [
            ['genre', '='],
        ]);

        $result = (new AdminRole())
            ->where($where)
            ->append(['genre_desc', 'status_desc'])
            ->paginate($limit);

        return Reply::table($result);
    }

    /**
     * @Auth("admin.role.info")
     * @Auth("admin.user")
     * @AuthMeta("获取角色信息")
     * @param int $genre
     * @return Response
     */
    public function select($genre = 0): Response
    {
        if (empty($genre)) {
            $where = null;
        } else {
            $where = function (Query $query) use ($genre) {
                $query->where('genre', '=', $genre);
            };
        }
        $result = AdminRole::buildOption(null, $where);
        return Reply::success($result);
    }

    /**
     * @Auth("admin.role.info")
     * @AuthMeta("获取角色信息")
     * @param int $id
     * @return Response
     * @throws DbException
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     */
    public function read(int $id): Response
    {
        $result = AdminRole::find($id);
        if (empty($result)) {
            return Reply::notFound();
        }
        return Reply::success($result);
    }

    /**
     * @Auth("admin.role.add")
     * @AuthMeta("创建角色信息")
     * @Validation("@Admin.Role")
     * @return Response
     */
    public function save(): Response
    {
        AdminRole::create($this->getFilterInput());
        return Reply::create();
    }

    /**
     * @Auth("admin.role.edit")
     * @AuthMeta("更改角色信息")
     * @Validation("@Admin.Role")
     * @param string|int $id
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function update($id): Response
    {
        $data = AdminRole::find($id);
        if (empty($data)) {
            return Reply::notFound();
        }
        $data->save($this->getFilterInput());
        return Reply::success();
    }

    /**
     * @Auth("admin.role.del")
     * @AuthMeta("删除角色信息")
     * @param string|int $id
     * @return Response
     */
    public function delete($id): Response
    {
        AdminRole::destroy($id);
        return Reply::success();
    }
}
