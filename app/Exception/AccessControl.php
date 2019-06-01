<?php
/**
 * Created by PhpStorm.
 * User: NHZEXG
 * Date: 2019/3/1
 * Time: 16:00
 */

namespace app\Exception;

use Throwable;

class AccessControl extends BusinessResult
{
    public function __construct(string $message = "", int $code = CODE_CONV_ACCESS_CONTROL, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
