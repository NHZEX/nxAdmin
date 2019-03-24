<?php

namespace app\controller;

class Index extends Base
{
    public function index()
    {
        $this->error('等待跳转 ~~~', '@index', '', 86400);
    }
}
