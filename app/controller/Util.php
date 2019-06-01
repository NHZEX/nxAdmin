<?php
/**
 * Created by PhpStorm.
 * User: NHZEXG
 * Date: 2019/1/10
 * Time: 11:20
 */

namespace app\controller;

class Util extends AdminBase
{
    public function obtainCsrfToken()
    {
        $token = $this->generateCsrfTokenSimple();
        $this->addCsrfToken($token);
        return self::showData(CODE_SUCCEED, $token);
    }
}
