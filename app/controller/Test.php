<?php
declare(strict_types=1);

namespace app\controller;

class Test extends Base
{
    public function index($type)
    {
        dump(__METHOD__);
        dump(func_get_args());
    }

    public function article($id, $type)
    {
        dump(__METHOD__);
        dump(func_get_args());
    }

    public function id($id)
    {
        dump(__METHOD__);
        dump(func_get_args());
    }
}
