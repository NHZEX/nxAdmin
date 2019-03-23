<?php
/**
 * Created by PhpStorm.
 * Date: 2017/11/16
 * Time: 14:05
 */

namespace app\controller\admin;

use think\facade\Url;

class Index extends Base
{
    public function index()
    {
        $this->redirect(Url::build('@admin.login'));
    }
}
