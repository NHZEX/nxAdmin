<?php

namespace app\Logic\User;

use app\Helper\WhereHelper;
use app\Logic\Base;
use app\Model\AdminUser;
use think\Paginator;
use function trim;

class AdminUserLogic extends Base
{
    public function search(int $limit, array $params): Paginator
    {
        $where = WhereHelper::buildWhere($params, [
            ['genre', '='],
            ['role_id', '='],
            ['status', '=', 'empty' => '\issue'],
            ['username', 'like', fn ($val) => trim($val) . '%'],
            ['nickname', 'like', fn ($val) => trim($val) . '%'],
        ]);

        return (new AdminUser())
            ->where($where)
            ->with(['beRoleName'])
            ->append(['status_desc', 'genre_desc', 'avatar_data'])
            ->paginate($limit);
    }
}
