<?php

namespace app\Service\Validate;

use think\Service;

class ValidateService extends Service
{
    public function register()
    {
        $this->app->middleware->add(ValidateMiddleware::class, 'controller');
    }

    public function boot()
    {
    }
}
