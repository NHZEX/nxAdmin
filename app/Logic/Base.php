<?php
/**
 * Created by PhpStorm.
 * User: NHZEXG
 * Date: 2018/4/20
 * Time: 15:35
 */

namespace app\Logic;

use app\Traits\PrintAbnormal;
use Tp\Model\Traits\TransactionExtension;

abstract class Base
{
    use PrintAbnormal;
    use TransactionExtension;

    // 错误信息
    protected $errorMessage = null;

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
