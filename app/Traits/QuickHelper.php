<?php
declare(strict_types=1);

namespace app\Traits;

use Closure;

trait QuickHelper
{
    /**
     * @param callable $call
     * @return Closure
     */
    public static function callWrap(callable $call)
    {
        return Closure::fromCallable($call);
    }
}
