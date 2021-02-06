<?php

namespace app\controller;

use Util\Reply;

class Index extends Base
{
    public function index()
    {
        return Reply::html('<h1>welcome</h1>');
    }
}
