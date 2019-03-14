<?php
/**
 * Created by PhpStorm.
 * User: NHZEXG
 * Date: 2018/12/21
 * Time: 10:18
 */

namespace app\controller;

use app\common\traits\CsrfHelper;
use app\common\traits\ShowReturn;
use think\Controller;

abstract class AdminBase extends Controller
{
    use ShowReturn;
    use CsrfHelper;

    public function initialize()
    {
    }
}
