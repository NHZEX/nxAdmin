<?php

namespace app\Controller\admin;

use app\Logic\User\AdminUserLogic;
use app\Model\AdminUser;
use Util\Reply;
use Zxin\Think\Auth\Annotation\Auth;
use Zxin\Think\Auth\Annotation\AuthMeta;
use Zxin\Think\Route\Annotation\Group;
use Zxin\Think\Route\Annotation\Resource;
use Zxin\Think\Validate\Annotation\Validation;
use function trim;

/**
 * Class User
 */
#[Group('admin', registerSort: 3000)]
#[Resource('users')]
class User extends Base
{
    #[Auth("admin.user.info")]
    #[AuthMeta("获取用户信息")]
    public function index(int $limit = 1)
    {
        $result = (new AdminUserLogic())->search($limit, $this->request->param());

        return Reply::table($result);
    }

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

    #[Auth("admin.user.add")]
    #[AuthMeta("添加用户信息")]
    #[Validation(name: "@Admin.User", scene: "_")]
    public function save()
    {
        AdminUser::create($this->getFilterInput());

        return Reply::create();
    }

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

    #[Auth("admin.user.del")]
    #[AuthMeta("删除用户信息")]
    public function delete(int $id)
    {
        AdminUser::destroy($id);

        return Reply::success();
    }
}
