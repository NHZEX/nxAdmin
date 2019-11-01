<?php
/**
 * Created by PhpStorm.
 * User: NHZEXG
 * Date: 2018/11/22
 * Time: 16:24
 */

namespace app\controller\admin;

use app\Exception\JsonException;
use app\Facade\WebConv;
use app\Logic\AdminRole as AdminRoleLogic;
use app\Logic\SystemMenu;
use app\Model\AdminRole;
use app\Model\AdminUser;
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
     * @return mixed
     */
    public function index()
    {
        return view_current([
            'url_table' => url('table'),
            'url_page_edit' => url('pageEdit'),
            'url_update' => url('update'),
            'url_delete' => url('delete'),
            'url_permission' => url('permission'),
            'url_menu' => url('menu'),
            'manager_types' => self::FILTER_TYPE[WebConv::getUserGenre()],
        ]);
    }

    /**
     * 主页表单
     * @param int    $limit
     * @param string $type
     * @return Response
     * @throws DbException
     */
    public function table(int $limit = 1, string $type = 'system')
    {
        if (!isset(self::FILTER_TYPE[WebConv::getUserGenre()][$type])) {
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
     * @param null $id
     * @return mixed
     * @throws JsonException
     */
    public function permission($id = null)
    {
        $hashArr = AdminRoleLogic::getExtPermission($id);
        return view_current([
            'hashArr' => json_encode_throw_on_error($hashArr),
            'role_id' => $id,
            'url_table' => url('@admin.permission/nodeList'),
            'url_save' => url('savePermission'),
        ]);
    }

    /**
     * 保存角色权限
     * @return Response
     * @throws Throwable
     */
    public function savePermission()
    {
        $param = $this->request->param();
        AdminRoleLogic::savePermission($param['id'], $param['hashArr']);
        return self::showMsg(CODE_SUCCEED);
    }

    /**
     * 跳转到菜单分配子页面
     * @param int $id
     * @return mixed
     * @throws DataNotFoundException
     * @throws DbException
     * @throws JsonException
     * @throws ModelNotFoundException
     */
    public function menu($id = 0)
    {
        $response = [
            'parentId' => 'pid'
        ];
        $menuIds = AdminRoleLogic::getExtMenu($id);

        return view('public/dtree', [
            'url_save' => url('saveMenu', ['id' => $id]),
            'data' => json_encode_throw_on_error(SystemMenu::obtainMenus()),
            'check_ids' => json_encode_throw_on_error($menuIds),
            'response' => json_encode_throw_on_error($response),
        ]);
    }

    /**
     * 保存菜单权限
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     * @throws JsonException
     * @throws ModelNotFoundException
     */
    public function saveMenu()
    {
        $param = $this->request->param();
        // 保存选中的HASH
        AdminRoleLogic::saveMenu($param['id'], $param['ids']);
        return self::showMsg(CODE_SUCCEED);
    }

    /**
     * 删除用户
     * @param null $id
     * @return Response
     */
    public function delete($id = null)
    {
        $result = AdminRole::destroy($id);
        return self::showData(CODE_SUCCEED, $result);
    }
}
