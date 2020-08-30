<?php

use app\Model\AdminUser;

return [
    'provider' => AdminUser::class,
    'middleware' => null,
    'remember' => [
        'name'   => 'remember',
        'expire' => 604800,  // 7 day
    ],
];
