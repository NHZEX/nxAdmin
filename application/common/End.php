<?php
/**
 * Created by PhpStorm.
 * User: NHZEXG
 * Date: 2019/3/27
 * Time: 14:53
 */
declare(strict_types=1);

namespace app\common;

use app\Server\WebConv;
use think\facade\App;

class End
{
    public function run()
    {
        App::getInstance()->delete(WebConv::class);
    }
}
