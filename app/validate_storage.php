<?php
// update date: 2023-09-09T16:43:16+08:00
// hash: 09c9bd649b848dda3c7dc5503376b6e9
return [
    'app\\Controller\\admin\\Index' => [
        'login' => [
            'validate' => 'app\\Validate\\Login',
            'scene' => null,
        ],
    ],
    'app\\Controller\\admin\\Role' => [
        'save' => [
            'validate' => 'app\\Validate\\Admin\\Role',
            'scene' => null,
        ],
        'update' => [
            'validate' => 'app\\Validate\\Admin\\Role',
            'scene' => null,
        ],
    ],
    'app\\Controller\\admin\\User' => [
        'save' => [
            'validate' => 'app\\Validate\\Admin\\User',
            'scene' => 'save',
        ],
        'update' => [
            'validate' => 'app\\Validate\\Admin\\User',
            'scene' => 'update',
        ],
    ],
    'app\\Controller\\admin\\Users' => [
        'login' => [
            'validate' => 'app\\Validate\\Login',
            'scene' => null,
        ],
    ],
    'app\\Controller\\V2\\AdminV2\\Roles' => [
        'save' => [
            'validate' => 'app\\Validate\\Admin\\Role',
            'scene' => null,
        ],
        'update' => [
            'validate' => 'app\\Validate\\Admin\\Role',
            'scene' => null,
        ],
    ],
    'app\\Controller\\V2\\AdminV2\\Users' => [
        'save' => [
            'validate' => 'app\\Validate\\Admin\\User',
            'scene' => 'save',
        ],
        'update' => [
            'validate' => 'app\\Validate\\Admin\\User',
            'scene' => 'update',
        ],
        'resetPassword' => [
            'validate' => 'app\\Validate\\Admin\\User',
            'scene' => 'resetPasswod',
        ],
    ],
];
