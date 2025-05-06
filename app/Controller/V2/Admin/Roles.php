<?php

declare(strict_types=1);

namespace app\Controller\V2\Admin;

use app\Controller\admin\Base;
use app\Model\AdminRole;
use app\ReplyEx;
use think\db\Query;
use think\Response;
use Zxin\Think\Auth\Annotation\Auth;
use Zxin\Think\Auth\Annotation\AuthMeta;
use Zxin\Think\Route\Annotation\Group;
use Zxin\Think\Route\Annotation\Resource;
use Zxin\Think\Validate\Annotation\Validation;

#[Group('v2/admin', registerSort: 2900)]
#[Resource('roles')]
class Roles extends Base
{
    #[Auth('admin.role.info')]
    #[AuthMeta('获取角色信息')]
    public function index(int $limit = 1): Response
    {
        $where = $this->buildWhere($this->request->param(), [
            ['genre', '='],
            ['status', '=', 'empty' => '\issue'],
            ['name', 'like', fn ($val) => "%{$val}%", 'tf' => fn ($val) => trim((string) $val)],
        ]);

        $result = (new AdminRole())
            ->where($where)
            ->append(['genre_desc', 'status_desc'])
            ->paginate($limit);

        return ReplyEx::table($result);
    }

    #[Auth('admin.role.info')]
    #[Auth('admin.user')]
    #[AuthMeta('获取角色信息')]
    public function select($genre = 0): Response
    {
        if (empty($genre)) {
            $where = null;
        } else {
            $where = function (Query $query) use ($genre): void {
                $query->where('genre', '=', $genre);
            };
        }
        $result = AdminRole::buildOption(null, $where);

        return ReplyEx::success($result);
    }

    #[Auth('admin.role.info')]
    #[AuthMeta('获取角色信息')]
    public function read(int $id): Response
    {
        $result = AdminRole::find($id);
        if (empty($result)) {
            return ReplyEx::notFound();
        }

        return ReplyEx::success($result);
    }

    #[Auth('admin.role.add')]
    #[AuthMeta('创建角色信息')]
    #[Validation('@Admin.Role')]
    public function save(): Response
    {
        $data = $this->getFilterInput();
        $data['genre'] = AdminRole::GENRE_SYSTEM;
        AdminRole::create($data);

        return ReplyEx::create();
    }

    #[Auth('admin.role.edit')]
    #[AuthMeta('更改角色信息')]
    #[Validation('@Admin.Role')]
    public function update($id): Response
    {
        $data = AdminRole::find($id);
        if (empty($data)) {
            return ReplyEx::notFound();
        }
        $input = $this->getFilterInput();
        $input['genre'] ??= AdminRole::GENRE_SYSTEM;
        $data->save($input);

        return ReplyEx::success();
    }

    #[Auth('admin.role.del')]
    #[AuthMeta('删除角色信息')]
    public function delete($id): Response
    {
        AdminRole::destroy($id);

        return ReplyEx::success();
    }
}
