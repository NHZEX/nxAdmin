<?php

use app\Model\AdminUser;

return [
    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
        'api' => [
            'driver' => 'token',
            'provider' => 'users',
            'hash' => false,
        ],
    ],

    'providers' => [
        'users' => [
            // 'driver' => 'eloquent',
            'model' => AdminUser::class,
        ],
    ],
];
