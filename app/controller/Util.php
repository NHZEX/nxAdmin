<?php
/**
 * Created by PhpStorm.
 * User: NHZEXG
 * Date: 2019/1/10
 * Time: 11:20
 */

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
