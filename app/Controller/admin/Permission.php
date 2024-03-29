<?php

namespace app\Controller\admin;

use app\Service\Auth\AuthHelper;
use think\helper\Arr;
use think\Response;
use Util\Reply;
use Zxin\Think\Auth\Annotation\Auth;
use Zxin\Think\Auth\AuthScan;
use Zxin\Think\Auth\Permission as AuthPermission;
use Zxin\Think\Route\Annotation\Group;
use Zxin\Think\Route\Annotation\Resource;
use Zxin\Think\Route\Annotation\ResourceRule;
use function array_merge;
use function count;
use function is_array;
use function is_numeric;

#[Group('admin')]
#[Resource('permission')]
class Permission extends Base
{
    #[Auth("admin.permission.info")]
    public function index(AuthPermission $permission): Response
    {
        $data = $permission->getTree('__ROOT__', 1);

        return Reply::success($data);
    }

    #[Auth("admin.permission.info")]
    public function read(string $id, AuthPermission $permission): Response
    {
        if (($info = $permission->queryPermission($id)) === null) {
            return Reply::bad();
        }

        $allow = [];
        foreach ($info['allow'] ?? [] as $item) {
            $feature = $permission->queryFeature($item);
            if ($feature) {
                $allow[] =  [
                    'name' => $item,
                    'desc' => $feature['desc'],
                ];
            }
        }
        $info['allow'] = $allow;

        return Reply::success($info);
    }

    #[Auth("admin.permission.edit")]
    public function update(string $id, AuthScan $authScan, bool $batch = false): Response
    {
        if (!$this->allowAccess()) {
            return Reply::bad(CODE_CONV_ACCESS_CONTROL, '无权限执行该操作', null, 403);
        }

        if ($batch) {
            $list = $this->request->put('list');

            if (empty($list) || !is_array($list)) {
                return Reply::bad();
            }

            $perm = AuthPermission::getInstance();
            $permissions = $perm->getPermission();
            foreach ($list as $name => $item) {
                $item = Arr::only($item, ['sort', 'desc']);
                if (count($item) === 0 || !isset($permissions[$name])) {
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
                return Reply::bad();
            }
            if (!empty($input['sort'])) {
                $input['sort'] = (int) $input['sort'];
            }

            $perm = AuthPermission::getInstance();
            if (!$perm->queryPermission($id)) {
                return Reply::notFound();
            }
            $permissions = $perm->getPermission();
            $permissions[$id] = array_merge($permissions[$id], $input);
            $perm->setPermission($permissions);
        }

        $authScan->export($perm->getStorage()->toArray());

        return Reply::success();
    }

    #[Auth("admin.permission.scan")]
    #[ResourceRule('scan', method: 'GET')]
    public function scan(AuthScan $authScan): Response
    {
        if (!$this->allowAccess()) {
            return Reply::bad(CODE_CONV_ACCESS_CONTROL, '无权限执行该操作', null, 403);
        }
        $authScan->refresh();
        return Reply::success();
    }

    private function allowAccess(): bool
    {
        return $this->app->isDebug() && AuthHelper::check() && AuthHelper::user()->isSuperAdmin();
    }
}
