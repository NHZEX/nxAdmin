<?php

namespace app\Exception;

use app\Traits\PrintAbnormal;
use app\Traits\ShowReturn;

/**
 * Class BusinessResult
 * @package app\common\exception
 *
 * 以异常的方式中断业务流程并抛出一个结果给上层调用者
 */
class BusinessResult extends ExceptionManager
{
    use ShowReturn;
    use PrintAbnormal;

    // 自定义字符串输出的样式
    public function __toString()
    {
        return "[#{$this->code}] {$this->message}";
    }
}
