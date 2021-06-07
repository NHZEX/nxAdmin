<?php
// update date: 2021-06-07T22:18:01+08:00
// hash: d93a5e0223c64e8c2bcd6b46dc198b2d
return [
    'app\\Controller\\api\\admin\\Index' => [
        'login' => [
            'validate' => 'app\\Validate\\Login',
            'scene' => null,
        ],
    ],
    'app\\Controller\\api\\admin\\Role' => [
        'save' => [
            'validate' => 'app\\Validate\\Admin\\Role',
            'scene' => null,
        ],
        'update' => [
            'validate' => 'app\\Validate\\Admin\\Role',
            'scene' => null,
        ],
    ],
    'app\\Controller\\api\\admin\\User' => [
        'save' => [
            'validate' => 'app\\Validate\\Admin\\User',
            'scene' => 'save',
        ],
        'update' => [
            'validate' => 'app\\Validate\\Admin\\User',
            'scene' => 'update',
        ],
    ],
];
