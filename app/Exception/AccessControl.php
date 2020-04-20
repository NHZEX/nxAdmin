<?php

namespace app\Exception;

use Throwable;

class AccessControl extends BusinessResult
{
    public function __construct(string $message = "", int $code = CODE_CONV_ACCESS_CONTROL, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
