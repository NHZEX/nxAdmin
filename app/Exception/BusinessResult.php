<?php

namespace app\Exception;

use app\Traits\PrintAbnormal;
use LogicException;

/**
 * Class BusinessResult
 * @package app\common\exception
 *
 * 以异常的方式中断业务流程并抛出一个结果给上层调用者
 */
class BusinessResult extends LogicException
{
    use PrintAbnormal;

    const FLAG_IGNORE_LOG = 0x01;

    protected $flag = 0;

    // 自定义字符串输出的样式
    public function __toString()
    {
        return "[#{$this->code}] {$this->message}";
    }

    public function setFlag(int $flag, bool $ignore = true)
    {
        if ($ignore) {
            $this->flag |= $flag;
        } elseif ($this->isFlag($flag)) {
            $this->flag ^= $flag;
        }

        return $this;
    }

    public function isFlag(int $flag): bool
    {
        return ($this->flag & $flag) === $flag;
    }
}
