<?php

namespace app\Logic;

use app\Exception\BusinessResult;

abstract class Base
{
    // 错误信息
    protected ?string $errorMessage = null;

    protected int $errorCode = 0;

    /**
     * @param string|null $message
     * @param int         $code
     * @return false
     */
    public function setLogicError(?string $message, int $code = 1): bool
    {
        $this->errorMessage = $message;
        $this->errorCode = $code;

        return false;
    }

    public function throwLogicError(?string $message, int $code = 1): void
    {
        throw new BusinessResult(message: $message, code: $code);
    }

    public function getErrorMessage(): string|null
    {
        return $this->errorMessage;
    }

    public function getErrorCode(): int
    {
        return $this->errorCode;
    }
}
