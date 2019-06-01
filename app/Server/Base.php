<?php
/**
 * Created by PhpStorm.
 * User: NHZEXG
 * Date: 2018/12/21
 * Time: 17:43
 */

namespace app\Server;

abstract class Base
{
    // 错误信息
    protected $errorMessage;

    /**
     * 返回模型的错误信息
     * @access public
     * @return string|array
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }
}
