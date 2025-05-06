<?php

declare(strict_types=1);

namespace app\Logic\User;

use app\Helper\WhereHelper;
use app\Logic\Base;
use app\Model\Admin\UserRoleRelationModel;
use app\Model\AdminUser;
use app\Traits\UseAppInstance;
use stdClass;
use think\db\Query;
use think\Paginator;
use function trim;

class AdminUserLogic extends Base
{
    use UseAppInstance;

    public function search(int $limit, array $params): Paginator
    {
        $where = WhereHelper::buildWhere($params, [
            ['genre', '='],
            // ['role_id', '='],
            ['status', '=', 'empty' => '\issue'],
            ['username', 'like', fn ($val) => trim($val).'%'],
            ['nickname', 'like', fn ($val) => trim($val).'%'],
        ]);

        $paginate = (new AdminUser())
            ->where($where)
            ->with(['beRoleName'])
            ->append(['status_desc', 'genre_desc', 'avatar_data'])
            ->paginate($limit);

        $collection = $paginate->getCollection();

        $userRolesGroup = UserRoleRelationModel::listUserRolesIdAndName($collection->column('id'));

        foreach ($collection as $item) {
            $item['roles'] = $userRolesGroup[$item->id] ?? [];
        }

        return $paginate;
    }

    public function read(int $id): ?AdminUser
    {
        $user = AdminUser::find($id);

        if (empty($user)) {
            return null;
        }

        $user['role_ids'] = UserRoleRelationModel::getUserRoles($user->id);

        return $user;
    }

    public function select(): array
    {
        return AdminUser::buildOption(
            ['id', 'username', 'nickname'],
            function (Query $query) {
                $query->whereIn('genre', [
                    AdminUser::GENRE_ADMIN,
                    AdminUser::GENRE_SUPER_ADMIN,
                    AdminUser::GENRE_OPERATOR,
                ]);
            },
        );
    }

    public function create(array $params): void
    {
        if (empty($params['extra'])) {
            $params['extra'] = new stdClass();
        }

        if (!empty($params['role_id'])) {
            $params['role_ids'] ??= [];
            $params['role_ids'][] = $params['role_id'];

            $params['role_id'] = 0;
        }

        // v2 兼容代码
        $params['password'] = hash('sha256', $params['password']);

        $user = AdminUser::create($params);

        if (isset($params['role_ids']) && \is_array($params['role_ids'])) {
            UserRoleRelationModel::setUserRoles($user->id, $params['role_ids']);
        }
    }

    public function update(int $id, array $params): void
    {
        if (empty($params['extra'])) {
            $params['extra'] = new stdClass();
        }

        $user = AdminUser::find($id);
        if (empty($user)) {
            self::throwLogicError('用户不存在');
        }

        if (isset($params['password'])) {
            if (empty(trim($params['password']))) {
                unset($params['password']);
            } else {
                // v2 兼容代码
                $params['password'] = hash('sha256', $params['password']);
            }
        }

        if (!empty($params['role_id'])) {
            $params['role_ids'] ??= [];
            $params['role_ids'][] = $params['role_id'];

            $params['role_id'] = 0;
        }
        $user->save($params);

        if (isset($params['role_ids']) && \is_array($params['role_ids'])) {
            UserRoleRelationModel::setUserRoles($user->id, $params['role_ids']);
        }
    }

    public function resetPassword(int $id, string $password): void
    {
        $user = AdminUser::find($id);
        if (empty($user)) {
            self::throwLogicError('用户不存在');
        }

        $password = hash('sha256', $password);
        $user->save([
            'password' => $password,
        ]);
    }
}
