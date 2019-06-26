<?php
/**
 * Created by PhpStorm.
 * User: NHZEXG
 * Date: 2018/4/28
 * Time: 15:49
 */

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

    public function abortMsg(array $header = [])
    {
        self::printAbnormalToLog($this, 'rbus');
        return self::showMsg($this->code, $this->__toString(), $header);
    }
}
