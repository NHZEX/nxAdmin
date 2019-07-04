<?php
declare(strict_types=1);

namespace app\controller\admin\Permission;

use app\controller\admin\Base;
use think\facade\View;

class Rule extends Base
{
    public function index()
    {
        View::assign([
            'url_permission_node' => url('@admin.permission.node/nodeList'),
        ]);

        return View::fetch();
    }
}
