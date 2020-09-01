<?php

use app\Model\AdminUser;
use app\Service\Auth\Middleware\Authorize;

return [
    'provider' => AdminUser::class,
    'middleware' => Authorize::class,
    'remember' => [
        'name'   => 'remember',
        'expire' => 604800,  // 7 day
    ],
];
