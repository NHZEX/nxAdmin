<?php

use app\Model\AdminUser;
use app\Service\Auth\Middleware\Authorize;
use app\Service\Auth\Record\RecordAdapter;

return [
    'provider' => AdminUser::class,
    'middleware' => Authorize::class,
    'remember' => [
        'name'   => 'remember',
        'expire' => 604800,  // 7 day
    ],
    'record' => [
        'adapter' => RecordAdapter::class,
    ],
];
