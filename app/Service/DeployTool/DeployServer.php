<?php

declare(strict_types=1);

namespace app\Service\DeployTool;

use think\Service;
use think\Validate;

class DeployServer extends Service
{
    public function register(): void
    {
        $this->commands(Deploy::class);

        Validate::maker(function (Validate $validate): void {
            // $validate->extend('writable', function () {});
        });
    }

    public function boot(): void
    {
    }
}
