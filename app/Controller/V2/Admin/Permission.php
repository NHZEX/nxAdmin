<?php

namespace app\Controller\V2\Admin;

use app\Controller\admin\Base;
use app\ReplyEx;
use app\Service\Auth\AuthHelper;
use think\helper\Arr;
use think\Response;
use Zxin\Think\Auth\Annotation\Auth;
use Zxin\Think\Auth\AuthScan;
use Zxin\Think\Auth\Permission as AuthPermission;
use Zxin\Think\Route\Annotation\Group;
use Zxin\Think\Route\Annotation\Route;
use function array_merge;
use function is_numeric;

#[Group('v2/admin/permission')]
class Permission extends Base
{
    #[Auth('admin.permission.info')]
    #[Route('tree', method: 'GET')]
    public function index(AuthPermission $permission): Response
    {
        $data = $permission->getTree('__ROOT__', 1);

        return ReplyEx::success($data);
    }

    #[Auth('admin.permission.scan')]
    #[Route('scan', method: 'POST')]
    public function scan(AuthScan $authScan): Response
    {
        if (!$this->allowAccess()) {
            return ReplyEx::bad(CODE_CONV_ACCESS_CONTROL, '无权限执行该操作', null, 403);
        }
        $authScan->refresh();

        return ReplyEx::success();
    }

    #[Auth('admin.permission.info')]
    #[Route(':id', method: 'GET', pattern: ['id' => '\S+'])]
    public function read(string $id, AuthPermission $permission): Response
    {
        if (($info = $permission->queryPermission($id)) === null) {
            return ReplyEx::bad();
        }

        $allow = [];
        foreach ($info['allow'] ?? [] as $item) {
            $feature = $permission->queryFeature($item);
            if ($feature) {
                $allow[] = [
                    'name' => $item,
                    'desc' => $feature['desc'],
                ];
            }
        }
        $info['allow'] = $allow;

        return ReplyEx::success($info);
    }

    #[Auth('admin.permission.edit')]
    #[Route(':id', method: 'PUT', pattern: ['id' => '\S+'])]
    public function update(string $id, AuthScan $authScan, bool $batch = false): Response
    {
        if (!$this->allowAccess()) {
            return ReplyEx::bad(CODE_CONV_ACCESS_CONTROL, '无权限执行该操作', null, 403);
        }

        if ($batch) {
            $list = $this->request->put('list');

            if (empty($list) || !\is_array($list)) {
                return ReplyEx::bad();
            }

            $perm = AuthPermission::getInstance();
            $permissions = $perm->getPermission();
            foreach ($list as $name => $item) {
                $item = Arr::only($item, ['sort', 'desc']);
                if (0 === \count($item) || !isset($permissions[$name])) {
                    continue;
                }
                if (isset($item['sort']) && is_numeric($item['sort'])) {
                    $item['sort'] = (int) $item['sort'];
                } else {
                    unset($item['sort']);
                }
                $permissions[$name] = array_merge($permissions[$name], $item);
            }
            $perm->setPermission($permissions);
        } else {
            $input = $this->request->only(['sort', 'desc']);

            if (empty($input)) {
                return ReplyEx::bad();
            }
            if (!empty($input['sort'])) {
                $input['sort'] = (int) $input['sort'];
            }

            $perm = AuthPermission::getInstance();
            if (!$perm->queryPermission($id)) {
                return ReplyEx::notFound();
            }
            $permissions = $perm->getPermission();
            $permissions[$id] = array_merge($permissions[$id], $input);
            $perm->setPermission($permissions);
        }

        $authScan->export($perm->getStorage()->toArray());

        return ReplyEx::success();
    }

    private function allowAccess(): bool
    {
        return $this->app->isDebug() && AuthHelper::check() && AuthHelper::user()->isSuperAdmin();
    }
}
