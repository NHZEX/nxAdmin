<?php
/**
 * Created by PhpStorm.
 * Date: 2017/11/16
 * Time: 14:05
 */

namespace app\controller\admin;

class Index extends Base
{
    public function index()
    {
        return redirect(url('@admin.login'));
    }
}
