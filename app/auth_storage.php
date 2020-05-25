<?php
// update date: 2020-05-25T11:56:16+08:00
// hash: a658ec323c9ac9b0a78898b4e2bb1fb2
return [
    'features' => [
        'node@api.admin.index/userinfo' => [
            'class' => 'app\\controller\\api\\admin\\Index::userInfo',
            'policy' => '',
            'desc' => '',
        ],
        'node@api.admin.permission/index' => [
            'class' => 'app\\controller\\api\\admin\\Permission::index',
            'policy' => '',
            'desc' => '',
        ],
        'node@api.admin.permission/read' => [
            'class' => 'app\\controller\\api\\admin\\Permission::read',
            'policy' => '',
            'desc' => '',
        ],
        'node@api.admin.permission/update' => [
            'class' => 'app\\controller\\api\\admin\\Permission::update',
            'policy' => '',
            'desc' => '',
        ],
        'node@api.admin.permission/scan' => [
            'class' => 'app\\controller\\api\\admin\\Permission::scan',
            'policy' => '',
            'desc' => '',
        ],
        'node@api.admin.role/index' => [
            'class' => 'app\\controller\\api\\admin\\Role::index',
            'policy' => '',
            'desc' => '',
        ],
        'node@api.admin.role/select' => [
            'class' => 'app\\controller\\api\\admin\\Role::select',
            'policy' => '',
            'desc' => '',
        ],
        'node@api.admin.role/read' => [
            'class' => 'app\\controller\\api\\admin\\Role::read',
            'policy' => '',
            'desc' => '',
        ],
        'node@api.admin.role/save' => [
            'class' => 'app\\controller\\api\\admin\\Role::save',
            'policy' => '',
            'desc' => '',
        ],
        'node@api.admin.role/update' => [
            'class' => 'app\\controller\\api\\admin\\Role::update',
            'policy' => '',
            'desc' => '',
        ],
        'node@api.admin.role/delete' => [
            'class' => 'app\\controller\\api\\admin\\Role::delete',
            'policy' => '',
            'desc' => '',
        ],
        'node@api.admin.user/index' => [
            'class' => 'app\\controller\\api\\admin\\User::index',
            'policy' => '',
            'desc' => '',
        ],
        'node@api.admin.user/read' => [
            'class' => 'app\\controller\\api\\admin\\User::read',
            'policy' => '',
            'desc' => '',
        ],
        'node@api.admin.user/save' => [
            'class' => 'app\\controller\\api\\admin\\User::save',
            'policy' => '',
            'desc' => '',
        ],
        'node@api.admin.user/update' => [
            'class' => 'app\\controller\\api\\admin\\User::update',
            'policy' => '',
            'desc' => '',
        ],
        'node@api.admin.user/delete' => [
            'class' => 'app\\controller\\api\\admin\\User::delete',
            'policy' => '',
            'desc' => '',
        ],
        'node@api.system/sysinfo' => [
            'class' => 'app\\controller\\api\\System::sysinfo',
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
                'node@api.admin.permission/update',
            ],
        ],
        'admin.permission.info' => [
            'pid' => 'admin.permission',
            'name' => 'admin.permission.info',
            'sort' => 3001,
            'desc' => '查看权限',
            'allow' => [
                'node@api.admin.permission/index',
                'node@api.admin.permission/read',
            ],
        ],
        'admin.permission.scan' => [
            'pid' => 'admin.permission',
            'name' => 'admin.permission.scan',
            'sort' => 3005,
            'desc' => '扫描权限',
            'allow' => [
                'node@api.admin.permission/scan',
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
                'node@api.admin.role/save',
            ],
        ],
        'admin.role.del' => [
            'pid' => 'admin.role',
            'name' => 'admin.role.del',
            'sort' => 2004,
            'desc' => '删除角色',
            'allow' => [
                'node@api.admin.role/delete',
            ],
        ],
        'admin.role.edit' => [
            'pid' => 'admin.role',
            'name' => 'admin.role.edit',
            'sort' => 2003,
            'desc' => '编辑角色',
            'allow' => [
                'node@api.admin.role/update',
            ],
        ],
        'admin.role.info' => [
            'pid' => 'admin.role',
            'name' => 'admin.role.info',
            'sort' => 2001,
            'desc' => '角色信息',
            'allow' => [
                'node@api.admin.role/index',
                'node@api.admin.role/select',
                'node@api.admin.role/read',
            ],
        ],
        'admin.user' => [
            'pid' => 'admin',
            'name' => 'admin.user',
            'sort' => 100,
            'desc' => '后台用户',
            'allow' => [
                'node@api.admin.role/select',
            ],
        ],
        'admin.user.add' => [
            'pid' => 'admin.user',
            'name' => 'admin.user.add',
            'sort' => 1002,
            'desc' => '添加用户',
            'allow' => [
                'node@api.admin.user/save',
            ],
        ],
        'admin.user.del' => [
            'pid' => 'admin.user',
            'name' => 'admin.user.del',
            'sort' => 1004,
            'desc' => '删除用户',
            'allow' => [
                'node@api.admin.user/delete',
            ],
        ],
        'admin.user.edit' => [
            'pid' => 'admin.user',
            'name' => 'admin.user.edit',
            'sort' => 1003,
            'desc' => '编辑用户',
            'allow' => [
                'node@api.admin.user/update',
            ],
        ],
        'admin.user.info' => [
            'pid' => 'admin.user',
            'name' => 'admin.user.info',
            'sort' => 1001,
            'desc' => '查看用户',
            'allow' => [
                'node@api.admin.user/index',
                'node@api.admin.user/read',
            ],
        ],
        'login' => [
            'pid' => '__ROOT__',
            'name' => 'login',
            'sort' => 0,
            'desc' => '用户登录后授予的权限',
            'allow' => [
                'node@api.admin.index/userinfo',
                'node@api.system/sysinfo',
            ],
        ],
    ],
    'permission2features' => [
        'admin' => [],
        'admin.permission' => [],
        'admin.permission.edit' => [
            'node@api.admin.permission/update',
        ],
        'admin.permission.info' => [
            'node@api.admin.permission/index',
            'node@api.admin.permission/read',
        ],
        'admin.permission.scan' => [
            'node@api.admin.permission/scan',
        ],
        'admin.role' => [],
        'admin.role.add' => [
            'node@api.admin.role/save',
        ],
        'admin.role.del' => [
            'node@api.admin.role/delete',
        ],
        'admin.role.edit' => [
            'node@api.admin.role/update',
        ],
        'admin.role.info' => [
            'node@api.admin.role/index',
            'node@api.admin.role/select',
            'node@api.admin.role/read',
        ],
        'admin.user' => [
            'node@api.admin.role/select',
        ],
        'admin.user.add' => [
            'node@api.admin.user/save',
        ],
        'admin.user.del' => [
            'node@api.admin.user/delete',
        ],
        'admin.user.edit' => [
            'node@api.admin.user/update',
        ],
        'admin.user.info' => [
            'node@api.admin.user/index',
            'node@api.admin.user/read',
        ],
        'login' => [
            'node@api.admin.index/userinfo',
            'node@api.system/sysinfo',
        ],
    ],
    'features2permission' => [
        'node@api.admin.permission/update' => [
            'admin.permission.edit' => true,
        ],
        'node@api.admin.permission/index' => [
            'admin.permission.info' => true,
        ],
        'node@api.admin.permission/read' => [
            'admin.permission.info' => true,
        ],
        'node@api.admin.permission/scan' => [
            'admin.permission.scan' => true,
        ],
        'node@api.admin.role/save' => [
            'admin.role.add' => true,
        ],
        'node@api.admin.role/delete' => [
            'admin.role.del' => true,
        ],
        'node@api.admin.role/update' => [
            'admin.role.edit' => true,
        ],
        'node@api.admin.role/index' => [
            'admin.role.info' => true,
        ],
        'node@api.admin.role/select' => [
            'admin.role.info' => true,
            'admin.user' => true,
        ],
        'node@api.admin.role/read' => [
            'admin.role.info' => true,
        ],
        'node@api.admin.user/save' => [
            'admin.user.add' => true,
        ],
        'node@api.admin.user/delete' => [
            'admin.user.del' => true,
        ],
        'node@api.admin.user/update' => [
            'admin.user.edit' => true,
        ],
        'node@api.admin.user/index' => [
            'admin.user.info' => true,
        ],
        'node@api.admin.user/read' => [
            'admin.user.info' => true,
        ],
        'node@api.admin.index/userinfo' => [
            'login' => true,
        ],
        'node@api.system/sysinfo' => [
            'login' => true,
        ],
    ],
];
