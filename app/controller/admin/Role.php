<?php
/**
 * Created by PhpStorm.
 * User: NHZEXG
 * Date: 2018/11/22
 * Time: 16:24
 */

namespace app\controller\admin;

use app\Logic\AdminRole as AdminRoleLogic;
use app\Model\AdminRole;
use app\Model\AdminUser;
use app\Service\Auth\Annotation\Auth;
use app\Service\Auth\AuthGuard;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\model\Collection;
use think\Response;
use Throwable;
use Tp\Model\Exception\ModelException;
use function view;
use function view_current;

class Role extends Base
{
    const FILTER_SYSTEM = 'system';
    const FILTER_AGENT = 'agent';
    const FILTER_TYPE = [
        AdminUser::GENRE_SUPER_ADMIN => [self::FILTER_SYSTEM => '系统', self::FILTER_AGENT => '代理'],
        AdminUser::GENRE_ADMIN => [self::FILTER_SYSTEM => '系统', self::FILTER_AGENT => '代理'],
        AdminUser::GENRE_AGENT => [self::FILTER_AGENT => '代理'],
    ];
    const FILTER_TYPE_MAPPING = [
        self::FILTER_SYSTEM => AdminRole::GENRE_SYSTEM,
        self::FILTER_AGENT => AdminRole::GENRE_AGENT,
    ];

    /**
     * 主页
     * @Auth("role.info")
     * @param AuthGuard $authGuard
     * @return mixed
     */
    public function index(AuthGuard $authGuard)
    {
        return view_current([
            'url_table' => url('table'),
            'url_page_edit' => url('pageEdit'),
            'url_update' => url('update'),
            'url_delete' => url('delete'),
            'url_permission' => url('permission'),
            'manager_types' => self::FILTER_TYPE[$authGuard->user()->genre],
        ]);
    }

    /**
     * 主页表单
     * @Auth("role.info")
     * @param AuthGuard $authGuard
     * @param int       $limit
     * @param string    $type
     * @return Response
     * @throws DbException
     */
    public function table(AuthGuard $authGuard, int $limit = 1, string $type = 'system')
    {
        if (!isset(self::FILTER_TYPE[$authGuard->user()->genre][$type])) {
            return self::showMsg(CODE_COM_PARAM, '无效的筛选参数');
        }
        $genre = self::FILTER_TYPE_MAPPING[$type];

        $result = (new AdminRole())
            ->where('genre', $genre)
            ->paginate($limit, false);
        /** @var Collection $collection */
        $collection = $result->getCollection();
        if (!$collection->isEmpty()) {
            $collection->append(['genre_desc', 'status_desc']);
        }
        return self::showTable($result);
    }

    /**
     * @Auth("role.info")
     * @param int|null    $base_pkid
     * @param string|null $type
     * @return string
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function pageEdit(int $base_pkid = null, ?string $type = null)
    {
        if ($base_pkid) {
            /** @var AdminRole $au */
            $au = (new AdminRole)->wherePk($base_pkid)->find();
            $params['csrf_update'] = $this->generateCsrfToken($au->id, $au->lock_version);
            $params['genre'] = $au->genre;
        } else {
            $params['csrf'] = $this->generateCsrfTokenSimple();
            $params['genre'] = self::FILTER_TYPE_MAPPING[$type] ?? null;
        }

        return view('edit', [
            'url_save' => url('save', $params),
            'genre_list' => $genres ?? [],
            'status_list' => AdminRole::STATUS_DICT,
            'edit_data' => $au ?? false,
        ]);
    }

    /**
     * @Auth("role.edit")
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function save()
    {
        $input = $this->request->param();
        $csrf = $this->getRequestCsrfToken();
        if ($csrf->isUpdate()) {
            [$pkid, $lock_version] = $this->parseCsrfToken($csrf);
            try {
                $ar = AdminRole::findOptimisticVer($pkid, $lock_version);
            } catch (ModelException $e) {
                return self::showException($e);
            }
            if (false === $ar instanceof AdminRole) {
                return self::showMsg(CODE_COM_DATA_NOT_EXIST, '数据不存在');
            }
        } else {
            $ar = new AdminRole();
        }
        $result = $ar->save($input);
        return self::showMsg(CODE_SUCCEED, $result);
    }

    /**
     * 权限分配
     * @Auth("role.info")
     * @param null $id
     * @return mixed
     */
    public function permission($id = null)
    {
        $permission = AdminRoleLogic::getExtPermission($id);
        return view_current([
            'selected' => $permission,
            'permission' => \app\Service\Auth\Model\Permission::getTree(),
            'role_id' => $id,
            'url_table' => url('admin.permission/nodeList'),
            'url_save' => url('savePermission'),
        ]);
    }

    /**
     * 保存角色权限
     * @Auth("role.edit")
     * @return Response
     * @throws Throwable
     */
    public function savePermission()
    {
        $param = $this->request->param();
        AdminRoleLogic::savePermission($param['id'], $param['permission']);
        return self::showMsg(CODE_SUCCEED);
    }

    /**
     * 删除用户
     * @Auth("role.del")
     * @param null $id
     * @return Response
     */
    public function delete($id = null)
    {
        $result = AdminRole::destroy($id);
        return self::showData(CODE_SUCCEED, $result);
    }
}
