<?php
// update date: 2023-03-30T00:49:42+08:00
// hash: 59b183166f0898fbe5c5f7142757ef21
return [
    [
        'file' => 'app/Controller/admin/Index.php',
        'class' => 'app\\Controller\\admin\\Index',
        'controller' => 'admin.Index',
        'sort' => 3000,
        'group' => \Zxin\Think\Route\Annotation\Group::__set_state([
            'name' => 'admin',
            'middleware' => null,
            'ext' => null,
            'deny_ext' => null,
            'https' => null,
            'domain' => null,
            'complete_match' => null,
            'cache' => null,
            'ajax' => null,
            'pjax' => null,
            'json' => null,
            'filter' => null,
            'append' => null,
            'pattern' => null,
            'registerSort' => 3000
        ]),
        'middleware' => [],
        'resource' => null,
        'resourceItems' => [],
        'routeItems' => [
            [
                'method' => 'login',
                'route' => [
                    \Zxin\Think\Route\Annotation\Route::__set_state([
                        'name' => null,
                        'method' => 'POST',
                        'middleware' => null,
                        'ext' => null,
                        'deny_ext' => null,
                        'https' => null,
                        'domain' => null,
                        'complete_match' => null,
                        'cache' => null,
                        'ajax' => null,
                        'pjax' => null,
                        'json' => null,
                        'filter' => null,
                        'append' => null,
                        'pattern' => null,
                        'setGroup' => null,
                        'registerSort' => 1000
                    ])
                ],
                'middleware' => []
            ],
            [
                'method' => 'logout',
                'route' => [
                    \Zxin\Think\Route\Annotation\Route::__set_state([
                        'name' => null,
                        'method' => 'GET',
                        'middleware' => null,
                        'ext' => null,
                        'deny_ext' => null,
                        'https' => null,
                        'domain' => null,
                        'complete_match' => null,
                        'cache' => null,
                        'ajax' => null,
                        'pjax' => null,
                        'json' => null,
                        'filter' => null,
                        'append' => null,
                        'pattern' => null,
                        'setGroup' => null,
                        'registerSort' => 1000
                    ])
                ],
                'middleware' => []
            ],
            [
                'method' => 'userInfo',
                'route' => [
                    \Zxin\Think\Route\Annotation\Route::__set_state([
                        'name' => 'user-info',
                        'method' => 'GET',
                        'middleware' => null,
                        'ext' => null,
                        'deny_ext' => null,
                        'https' => null,
                        'domain' => null,
                        'complete_match' => null,
                        'cache' => null,
                        'ajax' => null,
                        'pjax' => null,
                        'json' => null,
                        'filter' => null,
                        'append' => null,
                        'pattern' => null,
                        'setGroup' => null,
                        'registerSort' => 1000
                    ])
                ],
                'middleware' => []
            ]
        ]
    ],
    [
        'file' => 'app/Controller/admin/User.php',
        'class' => 'app\\Controller\\admin\\User',
        'controller' => 'admin.User',
        'sort' => 3000,
        'group' => \Zxin\Think\Route\Annotation\Group::__set_state([
            'name' => 'admin',
            'middleware' => null,
            'ext' => null,
            'deny_ext' => null,
            'https' => null,
            'domain' => null,
            'complete_match' => null,
            'cache' => null,
            'ajax' => null,
            'pjax' => null,
            'json' => null,
            'filter' => null,
            'append' => null,
            'pattern' => null,
            'registerSort' => 3000
        ]),
        'middleware' => [],
        'resource' => \Zxin\Think\Route\Annotation\Resource::__set_state([
            'name' => 'users',
            'vars' => null,
            'only' => null,
            'except' => null,
            'ext' => null,
            'deny_ext' => null,
            'https' => null,
            'domain' => null,
            'completeMatch' => null,
            'cache' => null,
            'ajax' => null,
            'pjax' => null,
            'json' => null,
            'filter' => null,
            'append' => null,
            'pattern' => null
        ]),
        'resourceItems' => [],
        'routeItems' => []
    ],
    [
        'file' => 'app/Controller/admin/Role.php',
        'class' => 'app\\Controller\\admin\\Role',
        'controller' => 'admin.Role',
        'sort' => 2900,
        'group' => \Zxin\Think\Route\Annotation\Group::__set_state([
            'name' => 'admin',
            'middleware' => null,
            'ext' => null,
            'deny_ext' => null,
            'https' => null,
            'domain' => null,
            'complete_match' => null,
            'cache' => null,
            'ajax' => null,
            'pjax' => null,
            'json' => null,
            'filter' => null,
            'append' => null,
            'pattern' => null,
            'registerSort' => 2900
        ]),
        'middleware' => [],
        'resource' => \Zxin\Think\Route\Annotation\Resource::__set_state([
            'name' => 'roles',
            'vars' => null,
            'only' => null,
            'except' => null,
            'ext' => null,
            'deny_ext' => null,
            'https' => null,
            'domain' => null,
            'completeMatch' => null,
            'cache' => null,
            'ajax' => null,
            'pjax' => null,
            'json' => null,
            'filter' => null,
            'append' => null,
            'pattern' => null
        ]),
        'resourceItems' => [],
        'routeItems' => []
    ],
    [
        'file' => 'app/Controller/admin/Permission.php',
        'class' => 'app\\Controller\\admin\\Permission',
        'controller' => 'admin.Permission',
        'sort' => 1000,
        'group' => \Zxin\Think\Route\Annotation\Group::__set_state([
            'name' => 'admin',
            'middleware' => null,
            'ext' => null,
            'deny_ext' => null,
            'https' => null,
            'domain' => null,
            'complete_match' => null,
            'cache' => null,
            'ajax' => null,
            'pjax' => null,
            'json' => null,
            'filter' => null,
            'append' => null,
            'pattern' => null,
            'registerSort' => 1000
        ]),
        'middleware' => [],
        'resource' => \Zxin\Think\Route\Annotation\Resource::__set_state([
            'name' => 'permission',
            'vars' => null,
            'only' => null,
            'except' => null,
            'ext' => null,
            'deny_ext' => null,
            'https' => null,
            'domain' => null,
            'completeMatch' => null,
            'cache' => null,
            'ajax' => null,
            'pjax' => null,
            'json' => null,
            'filter' => null,
            'append' => null,
            'pattern' => null
        ]),
        'resourceItems' => [
            [
                'method' => 'scan',
                'attr' => \Zxin\Think\Route\Annotation\ResourceRule::__set_state([
                    'name' => 'scan',
                    'method' => 'GET'
                ])
            ]
        ],
        'routeItems' => []
    ],
    [
        'file' => 'app/Controller/Index.php',
        'class' => 'app\\Controller\\Index',
        'controller' => 'Index',
        'sort' => 1000,
        'group' => null,
        'middleware' => [],
        'resource' => null,
        'resourceItems' => [],
        'routeItems' => []
    ],
    [
        'file' => 'app/Controller/System.php',
        'class' => 'app\\Controller\\System',
        'controller' => 'System',
        'sort' => 1000,
        'group' => \Zxin\Think\Route\Annotation\Group::__set_state([
            'name' => 'system',
            'middleware' => null,
            'ext' => null,
            'deny_ext' => null,
            'https' => null,
            'domain' => null,
            'complete_match' => null,
            'cache' => null,
            'ajax' => null,
            'pjax' => null,
            'json' => null,
            'filter' => null,
            'append' => null,
            'pattern' => null,
            'registerSort' => 1000
        ]),
        'middleware' => [],
        'resource' => null,
        'resourceItems' => [],
        'routeItems' => [
            [
                'method' => 'config',
                'route' => [
                    \Zxin\Think\Route\Annotation\Route::__set_state([
                        'name' => null,
                        'method' => 'GET',
                        'middleware' => null,
                        'ext' => null,
                        'deny_ext' => null,
                        'https' => null,
                        'domain' => null,
                        'complete_match' => null,
                        'cache' => null,
                        'ajax' => null,
                        'pjax' => null,
                        'json' => null,
                        'filter' => null,
                        'append' => null,
                        'pattern' => null,
                        'setGroup' => null,
                        'registerSort' => 1000
                    ])
                ],
                'middleware' => []
            ],
            [
                'method' => 'sysinfo',
                'route' => [
                    \Zxin\Think\Route\Annotation\Route::__set_state([
                        'name' => null,
                        'method' => 'GET',
                        'middleware' => null,
                        'ext' => null,
                        'deny_ext' => null,
                        'https' => null,
                        'domain' => null,
                        'complete_match' => null,
                        'cache' => null,
                        'ajax' => null,
                        'pjax' => null,
                        'json' => null,
                        'filter' => null,
                        'append' => null,
                        'pattern' => null,
                        'setGroup' => null,
                        'registerSort' => 1000
                    ])
                ],
                'middleware' => []
            ],
            [
                'method' => 'database',
                'route' => [
                    \Zxin\Think\Route\Annotation\Route::__set_state([
                        'name' => null,
                        'method' => 'GET',
                        'middleware' => null,
                        'ext' => null,
                        'deny_ext' => null,
                        'https' => null,
                        'domain' => null,
                        'complete_match' => null,
                        'cache' => null,
                        'ajax' => null,
                        'pjax' => null,
                        'json' => null,
                        'filter' => null,
                        'append' => null,
                        'pattern' => null,
                        'setGroup' => null,
                        'registerSort' => 1000
                    ])
                ],
                'middleware' => []
            ],
            [
                'method' => 'captcha',
                'route' => [
                    \Zxin\Think\Route\Annotation\Route::__set_state([
                        'name' => null,
                        'method' => 'GET',
                        'middleware' => [],
                        'ext' => null,
                        'deny_ext' => null,
                        'https' => null,
                        'domain' => null,
                        'complete_match' => null,
                        'cache' => null,
                        'ajax' => null,
                        'pjax' => null,
                        'json' => null,
                        'filter' => null,
                        'append' => null,
                        'pattern' => null,
                        'setGroup' => null,
                        'registerSort' => 1000
                    ])
                ],
                'middleware' => [
                    \Zxin\Think\Route\Annotation\Middleware::__set_state([
                        'name' => 'think\\middleware\\Throttle',
                        'params' => [
                            [
                                'visit_rate' => '10/m'
                            ]
                        ]
                    ])
                ]
            ],
            [
                'method' => 'resetCache',
                'route' => [
                    \Zxin\Think\Route\Annotation\Route::__set_state([
                        'name' => null,
                        'method' => 'GET',
                        'middleware' => null,
                        'ext' => null,
                        'deny_ext' => null,
                        'https' => null,
                        'domain' => null,
                        'complete_match' => null,
                        'cache' => null,
                        'ajax' => null,
                        'pjax' => null,
                        'json' => null,
                        'filter' => null,
                        'append' => null,
                        'pattern' => null,
                        'setGroup' => null,
                        'registerSort' => 1000
                    ])
                ],
                'middleware' => []
            ]
        ]
    ],
    [
        'file' => 'app/Controller/Upload.php',
        'class' => 'app\\Controller\\Upload',
        'controller' => 'Upload',
        'sort' => 1000,
        'group' => null,
        'middleware' => [],
        'resource' => null,
        'resourceItems' => [],
        'routeItems' => []
    ],
    [
        'file' => 'app/Controller/Util.php',
        'class' => 'app\\Controller\\Util',
        'controller' => 'Util',
        'sort' => 1000,
        'group' => null,
        'middleware' => [],
        'resource' => null,
        'resourceItems' => [],
        'routeItems' => []
    ]
];
