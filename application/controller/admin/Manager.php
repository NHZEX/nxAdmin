<?php
/**
 * Created by PhpStorm.
 * User: NHZEXG
 * Date: 2018/11/12
 * Time: 14:59
 */

namespace app\controller\admin;

use app\model\AdminRole;
use app\model\AdminUser;
use db\exception\ModelException;
use facade\WebConv;

class Manager extends Base
{
    const FILTER_SYSTEM = 'system';
    const FILTER_AGENT = 'agent';
    const FILTER_TYPE = [
        AdminUser::GENRE_SUPER_ADMIN => [self::FILTER_SYSTEM => '系统', self::FILTER_AGENT => '代理'],
        AdminUser::GENRE_ADMIN => [self::FILTER_SYSTEM => '系统', self::FILTER_AGENT => '代理'],
        AdminUser::GENRE_AGENT => [self::FILTER_AGENT => '代理'],
    ];
    const FILTER_TYPE_MAPPING = [
        self::FILTER_SYSTEM => [AdminUser::GENRE_SUPER_ADMIN, AdminUser::GENRE_ADMIN],
        self::FILTER_AGENT => [AdminUser::GENRE_AGENT],
    ];
    const FILTER_TYPE_MAPPING_ROLE = [
        self::FILTER_SYSTEM => AdminRole::GENRE_SYSTEM,
        self::FILTER_AGENT => AdminRole::GENRE_AGENT,
    ];
    const MAPPING_GENRE_USER_TO_ROLE = [
        AdminUser::GENRE_SUPER_ADMIN => AdminRole::GENRE_SYSTEM,
        AdminUser::GENRE_ADMIN => AdminRole::GENRE_SYSTEM,
        AdminUser::GENRE_AGENT => AdminRole::GENRE_AGENT,
    ];

    /**
     * 主页
     * @return mixed
     */
    public function index()
    {
        $this->assign([
            'url_table' => url('table'),
            'url_page_edit' => url('pageEdit'),
            'url_change_password' => url('changePassword'),
            'url_save' => url('save'),
            'url_delete' => url('delete'),
            'manager_types' => self::FILTER_TYPE[WebConv::getSelf()->sess_user_genre],
        ]);
        return $this->fetch();
    }

    /**
     * @param int $page
     * @param int $limit
     * @param string $type
     * @return \think\Response
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function table(int $page = 1, int $limit = 1, string $type = 'system')
    {
        if (!isset(self::FILTER_TYPE[WebConv::getSelf()->sess_user_genre][$type])) {
            return self::showMsg(CODE_COM_PARAM);
        }
        $genre = self::FILTER_TYPE_MAPPING[$type];

        $result = (new AdminUser())->field(['password', 'delete_time'], true)
            ->whereIn('genre', $genre)
            ->paginate2($limit, $page, false);
        $collection = $result->getCollection();
        if (!$collection->isEmpty()) {
            $collection->load(['beRoleName']);
            $collection->append(['status_desc', 'genre_desc', 'avatar_data']);
        }
        return self::showTable($result);
    }

    /**
     * @param int|null    $base_pkid
     * @param string|null $type
     * @return \think\Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function pageEdit(?int $base_pkid = null, ?string $type = null)
    {
        if ($base_pkid) {
            /** @var AdminUser $au */
            $au = (new AdminUser)->wherePk($base_pkid)->find();
            $au->append(['avatar_data']);
            $au->hidden(['password']);
            $params['csrf_update'] = $this->generateCsrfToken($au->id, $au->lock_version);

            $genre_role = self::MAPPING_GENRE_USER_TO_ROLE[$au->genre] ?? 0;
        } else {
            $genre = self::FILTER_TYPE_MAPPING[$type];
            $genre_role = self::FILTER_TYPE_MAPPING_ROLE[$type];
            $genres = array_intersect_key(AdminUser::GENRE_DICT, array_flip($genre));

            if (WebConv::getSelf()->sess_user_genre !== AdminUser::GENRE_SUPER_ADMIN) {
                unset($genres[AdminUser::GENRE_SUPER_ADMIN]);
            }
            $params['csrf'] = $this->generateCsrfTokenSimple();
        }

        $this->assign([
            'url_save' => url('save', $params),
            'url_upload' => url('@upload/image'),
            'genre_list' => $genres ?? [],
            'role_list' => AdminRole::selectOption($genre_role),
            'status_list' => AdminUser::STATUS_DICT,
            'edit_data' => $au ?? false,
        ]);

        return $this->fetch('edit');
    }

    /**
     * @param int|null $pkid
     * @return \think\Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function changePassword(int $pkid = null)
    {
        /** @var AdminUser $au */
        $au = (new AdminUser)->wherePk($pkid)->find();
        if (!$au) {
            return self::showData(CODE_SUCCEED, 'null');
        }
        $token = $this->generateCsrfToken($au->id, $au->lock_version);
        $this->addCsrfToken($token);
        return self::showData(CODE_SUCCEED, $token);
    }

    /**
     * @return \think\Response
     * @throws ModelException
     */
    public function save()
    {
        $input = $this->request->param();
        $csrf = $this->getRequestCsrfToken();
        if ($csrf->isUpdate()) {
            $action = $this->request->param('action', false);
            [$pkid, $lock_version] = $this->parseCsrfToken($csrf);
            $au = AdminUser::getOptimisticVer($pkid, $lock_version);
            if (false === $au instanceof AdminUser) {
                return self::showMsg(CODE_COM_DATA_NOT_EXIST, '数据不存在');
            }
            if ('password' === $action) {
                $au->password = $input['password'];
                $input = [];
            }
            // 如果没有传输用户类型，则自动赋值
            isset($input['genre']) || $input['genre'] = WebConv::getSelf()->sess_user_genre;
        } else {
            $au = new AdminUser();
            $au->password = $input['password'];
            $au->genre = $input['genre'];
        }

        $au->save($input);
        return self::showMsg(CODE_SUCCEED);
    }

    /**
     * 删除用户
     * @param null $id
     * @return \think\Response
     */
    public function delete($id = null)
    {
        $result = AdminUser::destroy($id);
        return self::showData(CODE_SUCCEED, $result);
    }
}
