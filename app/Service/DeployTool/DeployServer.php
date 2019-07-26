<?php
declare(strict_types=1);

namespace app\Service\DeployTool;

use think\Service;
use think\Validate;

class DeployServer extends Service
{
    public function register()
    {
        $this->commands(Deploy::class);

        Validate::maker(function (Validate $validate) {
            // $validate->extend('writable', function () {});
        });
    }

    public function boot()
    {
    }
}
