<?php

declare(strict_types=1);

namespace app\Traits;

use Closure;

trait QuickHelper
{
    /**
     * @return Closure
     */
    public static function callWrap(callable $call)
    {
        return Closure::fromCallable($call);
    }
}
