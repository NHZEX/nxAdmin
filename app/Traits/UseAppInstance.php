<?php

declare(strict_types=1);

namespace app\Traits;

use think\App;

trait UseAppInstance
{
    public static function instance(): static
    {
        return App::getInstance()->make(static::class);
    }
}
