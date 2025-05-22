<?php
/** @noinspection ALL */
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
        'node@system/database' => [
            'class' => 'app\\Controller\\System::database',
            'policy' => '',
            'desc' => '',
        ],
        'node@system/resetcache' => [
            'class' => 'app\\Controller\\System::resetCache',
            'policy' => '',
            'desc' => '',
        ],
        'node@v2.admin.permission/index' => [
            'class' => 'app\\Controller\\V2\\Admin\\Permission::index',
            'policy' => '',
            'desc' => '',
        ],
        'node@v2.admin.permission/scan' => [
            'class' => 'app\\Controller\\V2\\Admin\\Permission::scan',
            'policy' => '',
            'desc' => '',
        ],
        'node@v2.admin.permission/read' => [
            'class' => 'app\\Controller\\V2\\Admin\\Permission::read',
            'policy' => '',
            'desc' => '',
        ],
        'node@v2.admin.permission/update' => [
            'class' => 'app\\Controller\\V2\\Admin\\Permission::update',
            'policy' => '',
            'desc' => '',
        ],
        'node@v2.admin.roles/index' => [
            'class' => 'app\\Controller\\V2\\Admin\\Roles::index',
            'policy' => '',
            'desc' => '获取角色信息',
        ],
        'node@v2.admin.roles/select' => [
            'class' => 'app\\Controller\\V2\\Admin\\Roles::select',
            'policy' => '',
            'desc' => '获取角色信息',
        ],
        'node@v2.admin.roles/read' => [
            'class' => 'app\\Controller\\V2\\Admin\\Roles::read',
            'policy' => '',
            'desc' => '获取角色信息',
        ],
        'node@v2.admin.roles/save' => [
            'class' => 'app\\Controller\\V2\\Admin\\Roles::save',
            'policy' => '',
            'desc' => '创建角色信息',
        ],
        'node@v2.admin.roles/update' => [
            'class' => 'app\\Controller\\V2\\Admin\\Roles::update',
            'policy' => '',
            'desc' => '更改角色信息',
        ],
        'node@v2.admin.roles/delete' => [
            'class' => 'app\\Controller\\V2\\Admin\\Roles::delete',
            'policy' => '',
            'desc' => '删除角色信息',
        ],
        'node@v2.admin.users/index' => [
            'class' => 'app\\Controller\\V2\\Admin\\Users::index',
            'policy' => '',
            'desc' => '获取用户信息',
        ],
        'node@v2.admin.users/select' => [
            'class' => 'app\\Controller\\V2\\Admin\\Users::select',
            'policy' => '',
            'desc' => '',
        ],
        'node@v2.admin.users/read' => [
            'class' => 'app\\Controller\\V2\\Admin\\Users::read',
            'policy' => '',
            'desc' => '获取用户信息',
        ],
        'node@v2.admin.users/save' => [
            'class' => 'app\\Controller\\V2\\Admin\\Users::save',
            'policy' => '',
            'desc' => '添加用户信息',
        ],
        'node@v2.admin.users/update' => [
            'class' => 'app\\Controller\\V2\\Admin\\Users::update',
            'policy' => '',
            'desc' => '更改用户信息',
        ],
        'node@v2.admin.users/resetpassword' => [
            'class' => 'app\\Controller\\V2\\Admin\\Users::resetPassword',
            'policy' => '',
            'desc' => '重置用户密码',
        ],
        'node@v2.admin.users/delete' => [
            'class' => 'app\\Controller\\V2\\Admin\\Users::delete',
            'policy' => '',
            'desc' => '删除用户信息',
        ],
        'node@v2.system/info' => [
            'class' => 'app\\Controller\\V2\\System::info',
            'policy' => '',
            'desc' => '',
        ],
        'node@v2.system/sysinfo' => [
            'class' => 'app\\Controller\\V2\\System::sysinfo',
            'policy' => '',
            'desc' => '',
        ],
        'node@v2.system/database' => [
            'class' => 'app\\Controller\\V2\\System::database',
            'policy' => '',
            'desc' => '',
        ],
        'node@v2.system/resetcache' => [
            'class' => 'app\\Controller\\V2\\System::resetCache',
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
            'allow' => [
                'node@system/database',
                'node@v2.system/database',
            ],
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
                'node@v2.admin.permission/update',
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
                'node@v2.admin.permission/index',
                'node@v2.admin.permission/read',
            ],
        ],
        'admin.permission.scan' => [
            'pid' => 'admin.permission',
            'name' => 'admin.permission.scan',
            'sort' => 3005,
            'desc' => '扫描权限',
            'allow' => [
                'node@admin.permission/scan',
                'node@v2.admin.permission/scan',
            ],
        ],
        'admin.resetCache' => [
            'pid' => 'admin',
            'name' => 'admin.resetCache',
            'sort' => 0,
            'desc' => '',
            'allow' => [
                'node@system/resetcache',
                'node@v2.system/resetcache',
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
                'node@v2.admin.roles/save',
            ],
        ],
        'admin.role.del' => [
            'pid' => 'admin.role',
            'name' => 'admin.role.del',
            'sort' => 2004,
            'desc' => '删除角色',
            'allow' => [
                'node@admin.role/delete',
                'node@v2.admin.roles/delete',
            ],
        ],
        'admin.role.edit' => [
            'pid' => 'admin.role',
            'name' => 'admin.role.edit',
            'sort' => 2003,
            'desc' => '编辑角色',
            'allow' => [
                'node@admin.role/update',
                'node@v2.admin.roles/update',
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
                'node@v2.admin.roles/index',
                'node@v2.admin.roles/select',
                'node@v2.admin.roles/read',
            ],
        ],
        'admin.user' => [
            'pid' => 'admin',
            'name' => 'admin.user',
            'sort' => 100,
            'desc' => '后台用户',
            'allow' => [
                'node@admin.role/select',
                'node@v2.admin.roles/select',
            ],
        ],
        'admin.user.add' => [
            'pid' => 'admin.user',
            'name' => 'admin.user.add',
            'sort' => 1002,
            'desc' => '添加用户',
            'allow' => [
                'node@admin.user/save',
                'node@v2.admin.users/save',
            ],
        ],
        'admin.user.del' => [
            'pid' => 'admin.user',
            'name' => 'admin.user.del',
            'sort' => 1004,
            'desc' => '删除用户',
            'allow' => [
                'node@admin.user/delete',
                'node@v2.admin.users/delete',
            ],
        ],
        'admin.user.edit' => [
            'pid' => 'admin.user',
            'name' => 'admin.user.edit',
            'sort' => 1003,
            'desc' => '编辑用户',
            'allow' => [
                'node@admin.user/update',
                'node@v2.admin.users/update',
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
                'node@v2.admin.users/index',
                'node@v2.admin.users/read',
            ],
        ],
        'admin.user.reset-password' => [
            'pid' => 'admin.user',
            'name' => 'admin.user.reset-password',
            'sort' => 0,
            'desc' => '',
            'allow' => [
                'node@v2.admin.users/resetpassword',
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
                'node@v2.admin.users/select',
                'node@v2.system/info',
                'node@v2.system/sysinfo',
            ],
        ],
    ],
    'permission2features' => [
        'admin' => [
            'node@system/database',
            'node@v2.system/database',
        ],
        'admin.permission' => [],
        'admin.permission.edit' => [
            'node@admin.permission/update',
            'node@v2.admin.permission/update',
        ],
        'admin.permission.info' => [
            'node@admin.permission/index',
            'node@admin.permission/read',
            'node@v2.admin.permission/index',
            'node@v2.admin.permission/read',
        ],
        'admin.permission.scan' => [
            'node@admin.permission/scan',
            'node@v2.admin.permission/scan',
        ],
        'admin.resetCache' => [
            'node@system/resetcache',
            'node@v2.system/resetcache',
        ],
        'admin.role' => [],
        'admin.role.add' => [
            'node@admin.role/save',
            'node@v2.admin.roles/save',
        ],
        'admin.role.del' => [
            'node@admin.role/delete',
            'node@v2.admin.roles/delete',
        ],
        'admin.role.edit' => [
            'node@admin.role/update',
            'node@v2.admin.roles/update',
        ],
        'admin.role.info' => [
            'node@admin.role/index',
            'node@admin.role/select',
            'node@admin.role/read',
            'node@v2.admin.roles/index',
            'node@v2.admin.roles/select',
            'node@v2.admin.roles/read',
        ],
        'admin.user' => [
            'node@admin.role/select',
            'node@v2.admin.roles/select',
        ],
        'admin.user.add' => [
            'node@admin.user/save',
            'node@v2.admin.users/save',
        ],
        'admin.user.del' => [
            'node@admin.user/delete',
            'node@v2.admin.users/delete',
        ],
        'admin.user.edit' => [
            'node@admin.user/update',
            'node@v2.admin.users/update',
        ],
        'admin.user.info' => [
            'node@admin.user/index',
            'node@admin.user/read',
            'node@v2.admin.users/index',
            'node@v2.admin.users/read',
        ],
        'admin.user.reset-password' => [
            'node@v2.admin.users/resetpassword',
        ],
        'login' => [
            'node@admin.index/userinfo',
            'node@system/sysinfo',
            'node@v2.admin.users/select',
            'node@v2.system/info',
            'node@v2.system/sysinfo',
        ],
    ],
    'features2permission' => [
        'node@system/database' => [
            'admin' => true,
        ],
        'node@v2.system/database' => [
            'admin' => true,
        ],
        'node@admin.permission/update' => [
            'admin.permission.edit' => true,
        ],
        'node@v2.admin.permission/update' => [
            'admin.permission.edit' => true,
        ],
        'node@admin.permission/index' => [
            'admin.permission.info' => true,
        ],
        'node@admin.permission/read' => [
            'admin.permission.info' => true,
        ],
        'node@v2.admin.permission/index' => [
            'admin.permission.info' => true,
        ],
        'node@v2.admin.permission/read' => [
            'admin.permission.info' => true,
        ],
        'node@admin.permission/scan' => [
            'admin.permission.scan' => true,
        ],
        'node@v2.admin.permission/scan' => [
            'admin.permission.scan' => true,
        ],
        'node@system/resetcache' => [
            'admin.resetCache' => true,
        ],
        'node@v2.system/resetcache' => [
            'admin.resetCache' => true,
        ],
        'node@admin.role/save' => [
            'admin.role.add' => true,
        ],
        'node@v2.admin.roles/save' => [
            'admin.role.add' => true,
        ],
        'node@admin.role/delete' => [
            'admin.role.del' => true,
        ],
        'node@v2.admin.roles/delete' => [
            'admin.role.del' => true,
        ],
        'node@admin.role/update' => [
            'admin.role.edit' => true,
        ],
        'node@v2.admin.roles/update' => [
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
        'node@v2.admin.roles/index' => [
            'admin.role.info' => true,
        ],
        'node@v2.admin.roles/select' => [
            'admin.role.info' => true,
            'admin.user' => true,
        ],
        'node@v2.admin.roles/read' => [
            'admin.role.info' => true,
        ],
        'node@admin.user/save' => [
            'admin.user.add' => true,
        ],
        'node@v2.admin.users/save' => [
            'admin.user.add' => true,
        ],
        'node@admin.user/delete' => [
            'admin.user.del' => true,
        ],
        'node@v2.admin.users/delete' => [
            'admin.user.del' => true,
        ],
        'node@admin.user/update' => [
            'admin.user.edit' => true,
        ],
        'node@v2.admin.users/update' => [
            'admin.user.edit' => true,
        ],
        'node@admin.user/index' => [
            'admin.user.info' => true,
        ],
        'node@admin.user/read' => [
            'admin.user.info' => true,
        ],
        'node@v2.admin.users/index' => [
            'admin.user.info' => true,
        ],
        'node@v2.admin.users/read' => [
            'admin.user.info' => true,
        ],
        'node@v2.admin.users/resetpassword' => [
            'admin.user.reset-password' => true,
        ],
        'node@admin.index/userinfo' => [
            'login' => true,
        ],
        'node@system/sysinfo' => [
            'login' => true,
        ],
        'node@v2.admin.users/select' => [
            'login' => true,
        ],
        'node@v2.system/info' => [
            'login' => true,
        ],
        'node@v2.system/sysinfo' => [
            'login' => true,
        ],
    ],
];
