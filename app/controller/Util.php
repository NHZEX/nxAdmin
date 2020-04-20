<?php

namespace app\controller;

use app\Traits\CsrfHelper;
use function func\reply\reply_succeed;

class Util extends Base
{
    use CsrfHelper;

    public function obtainCsrfToken()
    {
        $token = $this->generateCsrfTokenSimple();
        $this->addCsrfToken($token);

        return reply_succeed()->header(['X-CSRF-Token' => $token]);
    }
}
