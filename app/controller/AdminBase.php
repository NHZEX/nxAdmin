<?php
/**
 * Created by PhpStorm.
 * User: NHZEXG
 * Date: 2018/12/21
 * Time: 10:18
 */

namespace app\controller;

use app\BaseController;
use app\Traits\CsrfHelper;
use app\Traits\ShowReturn;
use think\App;
use think\View;

abstract class AdminBase extends BaseController
{
    use ShowReturn;
    use CsrfHelper;

    /**
     * @var View
     */
    protected $view;

    public function __construct(App $app, View $view)
    {
        parent::__construct($app);

        $this->view = $view;
    }
}
