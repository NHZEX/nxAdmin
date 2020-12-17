<?php
// update date: 2020-12-17T01:18:33+08:00
// hash: 93ae3ac7617587e6e6ea7a405de9871e
return [
    'app\\controller\\api\\admin\\Index' => [
        'login' => [
            'validate' => 'app\\Validate\\Login',
            'scene' => null,
        ],
    ],
    'app\\controller\\api\\admin\\Role' => [
        'save' => [
            'validate' => 'app\\Validate\\Admin\\Role',
            'scene' => null,
        ],
        'update' => [
            'validate' => 'app\\Validate\\Admin\\Role',
            'scene' => null,
        ],
    ],
    'app\\controller\\api\\admin\\User' => [
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
