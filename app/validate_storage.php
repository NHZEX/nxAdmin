<?php
// update date: 2021-07-02T16:52:09+08:00
// hash: dc3038588cc46feb2db2953d66d0c510
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
];
