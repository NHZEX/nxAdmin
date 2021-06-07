<?php

declare(strict_types=1);

namespace app\Service\DeployTool;

use think\Service;

class DeployServer extends Service
{
    public function register()
    {
        $this->commands(Deploy::class);
    }

    public function boot()
    {
    }
}
