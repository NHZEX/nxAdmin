<?php
/**
 * Created by PhpStorm.
 * User: NHZEXG
 * Date: 2018/12/21
 * Time: 10:18
 */

namespace app\controller;

use app\BaseController;
use app\common\Traits\CsrfHelper;
use app\common\Traits\ShowReturn;

abstract class AdminBase extends BaseController
{
    use ShowReturn;
    use CsrfHelper;

    public function initialize()
    {
    }
}
