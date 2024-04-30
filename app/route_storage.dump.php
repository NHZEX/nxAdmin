<?php
// update date: 2024-04-25T16:36:00+08:00
// hash: 52ebccb58e405b1c990b71f071a8be7e
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
        'file' => 'app/Controller/V2/Admin/Users.php',
        'class' => 'app\\Controller\\V2\\Admin\\Users',
        'controller' => 'V2.Admin.Users',
        'sort' => 3000,
        'group' => \Zxin\Think\Route\Annotation\Group::__set_state([
            'name' => 'v2/admin',
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
        'resourceItems' => [
            [
                'method' => 'resetPassword',
                'attr' => \Zxin\Think\Route\Annotation\ResourceRule::__set_state([
                    'name' => ':id/reset-password',
                    'method' => 'POST'
                ])
            ]
        ],
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
        'file' => 'app/Controller/V2/Admin/Roles.php',
        'class' => 'app\\Controller\\V2\\Admin\\Roles',
        'controller' => 'V2.Admin.Roles',
        'sort' => 2900,
        'group' => \Zxin\Think\Route\Annotation\Group::__set_state([
            'name' => 'v2/admin',
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
    ],
    [
        'file' => 'app/Controller/V2/Admin/Permission.php',
        'class' => 'app\\Controller\\V2\\Admin\\Permission',
        'controller' => 'V2.Admin.Permission',
        'sort' => 1000,
        'group' => \Zxin\Think\Route\Annotation\Group::__set_state([
            'name' => 'v2/admin/permission',
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
                'method' => 'index',
                'route' => [
                    \Zxin\Think\Route\Annotation\Route::__set_state([
                        'name' => 'tree',
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
                'method' => 'scan',
                'route' => [
                    \Zxin\Think\Route\Annotation\Route::__set_state([
                        'name' => 'scan',
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
                'method' => 'read',
                'route' => [
                    \Zxin\Think\Route\Annotation\Route::__set_state([
                        'name' => ':id',
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
                        'pattern' => [
                            'id' => '\\S+'
                        ],
                        'setGroup' => null,
                        'registerSort' => 1000
                    ])
                ],
                'middleware' => []
            ],
            [
                'method' => 'update',
                'route' => [
                    \Zxin\Think\Route\Annotation\Route::__set_state([
                        'name' => ':id',
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
                        'pattern' => [
                            'id' => '\\S+'
                        ],
                        'setGroup' => null,
                        'registerSort' => 1000
                    ])
                ],
                'middleware' => []
            ]
        ]
    ],
    [
        'file' => 'app/Controller/V2/Index.php',
        'class' => 'app\\Controller\\V2\\Index',
        'controller' => 'V2.Index',
        'sort' => 1000,
        'group' => null,
        'middleware' => [],
        'resource' => null,
        'resourceItems' => [],
        'routeItems' => [
            [
                'method' => 'captcha',
                'route' => [
                    \Zxin\Think\Route\Annotation\Route::__set_state([
                        'name' => 'v2/captcha',
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
                'method' => 'login',
                'route' => [
                    \Zxin\Think\Route\Annotation\Route::__set_state([
                        'name' => 'v2/login',
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
                        'name' => 'v2/logout',
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
        'file' => 'app/Controller/V2/System.php',
        'class' => 'app\\Controller\\V2\\System',
        'controller' => 'V2.System',
        'sort' => 1000,
        'group' => \Zxin\Think\Route\Annotation\Group::__set_state([
            'name' => 'v2/system',
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
                'method' => 'info',
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
    ]
];
