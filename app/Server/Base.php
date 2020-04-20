<?php

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
