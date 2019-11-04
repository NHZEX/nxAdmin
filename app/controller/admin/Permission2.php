<?php
declare(strict_types=1);

namespace app\controller\admin;

use app\Service\Auth\ControllerScan;

class Permission2 extends Base
{
    public function index()
    {
        return view_current();
    }


    public function edit(ControllerScan $scan)
    {
        return view_current([
            'node' => tree_to_table($scan->nodeTree()),
        ]);
    }

    public function save()
    {

    }
}
