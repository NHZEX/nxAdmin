<?php

namespace app\controller;

class Index extends Base
{
    protected $middleware = [];

    public function index()
    {
        $this->success('等待跳转 ~~~', '@index', '', 86400);
    }
}
