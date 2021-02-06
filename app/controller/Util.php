<?php

namespace app\controller;

use app\Traits\CsrfHelper;
use Util\Reply;

class Util extends Base
{
    use CsrfHelper;

    public function obtainCsrfToken()
    {
        $token = $this->generateCsrfTokenSimple();
        $this->addCsrfToken($token);

        return Reply::success()->header(['X-CSRF-Token' => $token]);
    }
}
