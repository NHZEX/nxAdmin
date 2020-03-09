<?php

namespace app\controller;

use function func\reply\reply_html;

class Index extends Base
{
    public function index()
    {
        return reply_html('<h1>welcome</h1>');
    }
}
