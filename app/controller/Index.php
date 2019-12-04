<?php

namespace app\controller;

class Index extends Base
{
    public function index()
    {
        return $this->success('welcome', '@index', '', 86400);
    }
}
