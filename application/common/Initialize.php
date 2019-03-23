<?php
/**
 * Created by PhpStorm.
 * User: NHZEXG
 * Date: 2019/3/20
 * Time: 13:58
 */

namespace app\common;

use think\App;

class Initialize
{
    public function run(App $app)
    {
        if ($app->isDebug()) {
            ini_set('xdebug.var_display_max_depth', '10');
            ini_set('xdebug.var_display_max_children', '256');
            ini_set('xdebug.var_display_max_data', '1024');
        }
    }
}
