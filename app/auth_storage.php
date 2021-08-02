<?php
// update date: 2021-07-02T17:46:31+08:00
// hash: 5c5fb0c176d47e683ca34445261633bf
return [
    'features' => [
        'node@admin.index/userinfo' => [
            'class' => 'app\\Controller\\admin\\Index::userInfo',
            'policy' => '',
            'desc' => '',
        ],
        'node@admin.permission/index' => [
            'class' => 'app\\Controller\\admin\\Permission::index',
            'policy' => '',
            'desc' => '',
        ],
        'node@admin.permission/read' => [
            'class' => 'app\\Controller\\admin\\Permission::read',
            'policy' => '',
            'desc' => '',
        ],
        'node@admin.permission/update' => [
            'class' => 'app\\Controller\\admin\\Permission::update',
            'policy' => '',
            'desc' => '',
        ],
        'node@admin.permission/scan' => [
            'class' => 'app\\Controller\\admin\\Permission::scan',
            'policy' => '',
            'desc' => '',
        ],
        'node@admin.role/index' => [
            'class' => 'app\\Controller\\admin\\Role::index',
            'policy' => '',
            'desc' => '获取角色信息',
        ],
        'node@admin.role/select' => [
            'class' => 'app\\Controller\\admin\\Role::select',
            'policy' => '',
            'desc' => '获取角色信息',
        ],
        'node@admin.role/read' => [
            'class' => 'app\\Controller\\admin\\Role::read',
            'policy' => '',
            'desc' => '获取角色信息',
        ],
        'node@admin.role/save' => [
            'class' => 'app\\Controller\\admin\\Role::save',
            'policy' => '',
            'desc' => '创建角色信息',
        ],
        'node@admin.role/update' => [
            'class' => 'app\\Controller\\admin\\Role::update',
            'policy' => '',
            'desc' => '更改角色信息',
        ],
        'node@admin.role/delete' => [
            'class' => 'app\\Controller\\admin\\Role::delete',
            'policy' => '',
            'desc' => '删除角色信息',
        ],
        'node@admin.user/index' => [
            'class' => 'app\\Controller\\admin\\User::index',
            'policy' => '',
            'desc' => '获取用户信息',
        ],
        'node@admin.user/read' => [
            'class' => 'app\\Controller\\admin\\User::read',
            'policy' => '',
            'desc' => '获取用户信息',
        ],
        'node@admin.user/save' => [
            'class' => 'app\\Controller\\admin\\User::save',
            'policy' => '',
            'desc' => '添加用户信息',
        ],
        'node@admin.user/update' => [
            'class' => 'app\\Controller\\admin\\User::update',
            'policy' => '',
            'desc' => '更改用户信息',
        ],
        'node@admin.user/delete' => [
            'class' => 'app\\Controller\\admin\\User::delete',
            'policy' => '',
            'desc' => '删除用户信息',
        ],
        'node@system/sysinfo' => [
            'class' => 'app\\Controller\\System::sysinfo',
            'policy' => '',
            'desc' => '',
        ],
    ],
    'permission' => [
        'admin' => [
            'pid' => '__ROOT__',
            'name' => 'admin',
            'sort' => 1,
            'desc' => 'ADMIN',
            'allow' => null,
        ],
        'admin.permission' => [
            'pid' => 'admin',
            'name' => 'admin.permission',
            'sort' => 300,
            'desc' => '后台权限',
            'allow' => null,
        ],
        'admin.permission.edit' => [
            'pid' => 'admin.permission',
            'name' => 'admin.permission.edit',
            'sort' => 3002,
            'desc' => '编辑权限',
            'allow' => [
                'node@admin.permission/update',
            ],
        ],
        'admin.permission.info' => [
            'pid' => 'admin.permission',
            'name' => 'admin.permission.info',
            'sort' => 3001,
            'desc' => '查看权限',
            'allow' => [
                'node@admin.permission/index',
                'node@admin.permission/read',
            ],
        ],
        'admin.permission.scan' => [
            'pid' => 'admin.permission',
            'name' => 'admin.permission.scan',
            'sort' => 3005,
            'desc' => '扫描权限',
            'allow' => [
                'node@admin.permission/scan',
            ],
        ],
        'admin.role' => [
            'pid' => 'admin',
            'name' => 'admin.role',
            'sort' => 200,
            'desc' => '后台角色',
            'allow' => null,
        ],
        'admin.role.add' => [
            'pid' => 'admin.role',
            'name' => 'admin.role.add',
            'sort' => 2002,
            'desc' => '添加角色',
            'allow' => [
                'node@admin.role/save',
            ],
        ],
        'admin.role.del' => [
            'pid' => 'admin.role',
            'name' => 'admin.role.del',
            'sort' => 2004,
            'desc' => '删除角色',
            'allow' => [
                'node@admin.role/delete',
            ],
        ],
        'admin.role.edit' => [
            'pid' => 'admin.role',
            'name' => 'admin.role.edit',
            'sort' => 2003,
            'desc' => '编辑角色',
            'allow' => [
                'node@admin.role/update',
            ],
        ],
        'admin.role.info' => [
            'pid' => 'admin.role',
            'name' => 'admin.role.info',
            'sort' => 2001,
            'desc' => '角色信息',
            'allow' => [
                'node@admin.role/index',
                'node@admin.role/select',
                'node@admin.role/read',
            ],
        ],
        'admin.user' => [
            'pid' => 'admin',
            'name' => 'admin.user',
            'sort' => 100,
            'desc' => '后台用户',
            'allow' => [
                'node@admin.role/select',
            ],
        ],
        'admin.user.add' => [
            'pid' => 'admin.user',
            'name' => 'admin.user.add',
            'sort' => 1002,
            'desc' => '添加用户',
            'allow' => [
                'node@admin.user/save',
            ],
        ],
        'admin.user.del' => [
            'pid' => 'admin.user',
            'name' => 'admin.user.del',
            'sort' => 1004,
            'desc' => '删除用户',
            'allow' => [
                'node@admin.user/delete',
            ],
        ],
        'admin.user.edit' => [
            'pid' => 'admin.user',
            'name' => 'admin.user.edit',
            'sort' => 1003,
            'desc' => '编辑用户',
            'allow' => [
                'node@admin.user/update',
            ],
        ],
        'admin.user.info' => [
            'pid' => 'admin.user',
            'name' => 'admin.user.info',
            'sort' => 1001,
            'desc' => '查看用户',
            'allow' => [
                'node@admin.user/index',
                'node@admin.user/read',
            ],
        ],
        'login' => [
            'pid' => '__ROOT__',
            'name' => 'login',
            'sort' => 0,
            'desc' => '用户登录后授予的权限',
            'allow' => [
                'node@admin.index/userinfo',
                'node@system/sysinfo',
            ],
        ],
    ],
    'permission2features' => [
        'admin' => [],
        'admin.permission' => [],
        'admin.permission.edit' => [
            'node@admin.permission/update',
        ],
        'admin.permission.info' => [
            'node@admin.permission/index',
            'node@admin.permission/read',
        ],
        'admin.permission.scan' => [
            'node@admin.permission/scan',
        ],
        'admin.role' => [],
        'admin.role.add' => [
            'node@admin.role/save',
        ],
        'admin.role.del' => [
            'node@admin.role/delete',
        ],
        'admin.role.edit' => [
            'node@admin.role/update',
        ],
        'admin.role.info' => [
            'node@admin.role/index',
            'node@admin.role/select',
            'node@admin.role/read',
        ],
        'admin.user' => [
            'node@admin.role/select',
        ],
        'admin.user.add' => [
            'node@admin.user/save',
        ],
        'admin.user.del' => [
            'node@admin.user/delete',
        ],
        'admin.user.edit' => [
            'node@admin.user/update',
        ],
        'admin.user.info' => [
            'node@admin.user/index',
            'node@admin.user/read',
        ],
        'login' => [
            'node@admin.index/userinfo',
            'node@system/sysinfo',
        ],
    ],
    'features2permission' => [
        'node@admin.permission/update' => [
            'admin.permission.edit' => true,
        ],
        'node@admin.permission/index' => [
            'admin.permission.info' => true,
        ],
        'node@admin.permission/read' => [
            'admin.permission.info' => true,
        ],
        'node@admin.permission/scan' => [
            'admin.permission.scan' => true,
        ],
        'node@admin.role/save' => [
            'admin.role.add' => true,
        ],
        'node@admin.role/delete' => [
            'admin.role.del' => true,
        ],
        'node@admin.role/update' => [
            'admin.role.edit' => true,
        ],
        'node@admin.role/index' => [
            'admin.role.info' => true,
        ],
        'node@admin.role/select' => [
            'admin.role.info' => true,
            'admin.user' => true,
        ],
        'node@admin.role/read' => [
            'admin.role.info' => true,
        ],
        'node@admin.user/save' => [
            'admin.user.add' => true,
        ],
        'node@admin.user/delete' => [
            'admin.user.del' => true,
        ],
        'node@admin.user/update' => [
            'admin.user.edit' => true,
        ],
        'node@admin.user/index' => [
            'admin.user.info' => true,
        ],
        'node@admin.user/read' => [
            'admin.user.info' => true,
        ],
        'node@admin.index/userinfo' => [
            'login' => true,
        ],
        'node@system/sysinfo' => [
            'login' => true,
        ],
    ],
];
