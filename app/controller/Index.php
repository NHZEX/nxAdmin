<?php

namespace app\controller;

class Index extends Base
{
    public function index()
    {
        $this->success('等待跳转 ~~~', '@index', '', 86400);
    }
}
