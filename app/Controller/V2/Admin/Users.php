<?php
declare(strict_types=1);

namespace app\Controller\V2\Admin;

use app\Controller\admin\Base;
use app\Logic\User\AdminUserLogic;
use app\Model\AdminUser;
use app\ReplyEx;
use app\Validate\Admin\User;
use think\Response;
use Util\Reply;
use Zxin\Think\Auth\Annotation\Auth;
use Zxin\Think\Auth\Annotation\AuthMeta;
use Zxin\Think\Route\Annotation\Group;
use Zxin\Think\Route\Annotation\Resource;
use Zxin\Think\Route\Annotation\ResourceRule;
use Zxin\Think\Validate\Annotation\Validation;
use function trim;

#[Group('v2/admin', registerSort: 3000)]
#[Resource('users')]
class Users extends Base
{
    #[Auth("admin.user.info")]
    #[AuthMeta("获取用户信息")]
    public function index(int $limit = 1): Response
    {
        $result = (new AdminUserLogic())->search($limit, $this->request->param());

        return ReplyEx::table($result);
    }

    #[Auth("admin.user.info")]
    #[AuthMeta("获取用户信息")]
    public function read(int $id): Response
    {
        $result = (new AdminUserLogic())->read($id);
        if (empty($result)) {
            return Reply::notFound();
        }
        return ReplyEx::success($result);
    }

    #[Auth("admin.user.add")]
    #[AuthMeta("添加用户信息")]
    #[Validation(name: User::class, scene: "_")]
    public function save(): Response
    {
        $data = $this->getFilterInput();
        (new AdminUserLogic())->create($data);

        return ReplyEx::create();
    }

    #[Auth("admin.user.edit")]
    #[AuthMeta("更改用户信息")]
    #[Validation(name: User::class, scene: "_")]
    public function update(int $id): Response
    {
        $data = $this->getFilterInput();
        (new AdminUserLogic())->update($id, $data);

        return ReplyEx::success();
    }

    #[Auth("admin.user.reset-password")]
    #[AuthMeta("重置用户密码")]
    #[ResourceRule(':id/reset-password', 'POST')]
    #[Validation(name: User::class, scene: "resetPasswod")]
    public function resetPassword(int $id): Response
    {
        $password = $this->request->param('password');
        $password = trim($password);

        if (empty($password)) {
            return ReplyEx::bad(code: 1, message: '密码不能为空');
        }

        (new AdminUserLogic())->resetPassword($id, $password);

        return ReplyEx::success();
    }

    #[Auth("admin.user.del")]
    #[AuthMeta("删除用户信息")]
    public function delete(int $id): Response
    {
        AdminUser::destroy($id);

        return ReplyEx::success();
    }
}
