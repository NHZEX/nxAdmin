<?php
// update date: 2020-03-04T00:20:27+08:00
// hash: b63402768dbda2bb3df4e8a421529404
return [
    'features' => [
        'node@admin.main/index' => [
            'class' => 'app\\controller\\admin\\Main::index',
            'policy' => '',
            'desc' => '',
        ],
        'node@admin.main/sysinfo' => [
            'class' => 'app\\controller\\admin\\Main::sysinfo',
            'policy' => '',
            'desc' => '',
        ],
        'node@api.admin.auth/userinfo' => [
            'class' => 'app\\controller\\api\\admin\\Auth::userInfo',
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
        'node@api.admin.permission/scan' => [
            'class' => 'app\\controller\\api\\admin\\Permission::scan',
            'policy' => '',
            'desc' => '',
        ],
        'node@api.admin.roles/index' => [
            'class' => 'app\\controller\\api\\admin\\Roles::index',
            'policy' => '',
            'desc' => '',
        ],
        'node@api.admin.roles/select' => [
            'class' => 'app\\controller\\api\\admin\\Roles::select',
            'policy' => '',
            'desc' => '',
        ],
        'node@api.admin.roles/read' => [
            'class' => 'app\\controller\\api\\admin\\Roles::read',
            'policy' => '',
            'desc' => '',
        ],
        'node@api.admin.roles/save' => [
            'class' => 'app\\controller\\api\\admin\\Roles::save',
            'policy' => '',
            'desc' => '',
        ],
        'node@api.admin.roles/update' => [
            'class' => 'app\\controller\\api\\admin\\Roles::update',
            'policy' => '',
            'desc' => '',
        ],
        'node@api.admin.roles/delete' => [
            'class' => 'app\\controller\\api\\admin\\Roles::delete',
            'policy' => '',
            'desc' => '',
        ],
        'node@api.admin.users/index' => [
            'class' => 'app\\controller\\api\\admin\\Users::index',
            'policy' => '',
            'desc' => '',
        ],
        'node@api.admin.users/read' => [
            'class' => 'app\\controller\\api\\admin\\Users::read',
            'policy' => '',
            'desc' => '',
        ],
        'node@api.admin.users/save' => [
            'class' => 'app\\controller\\api\\admin\\Users::save',
            'policy' => '',
            'desc' => '',
        ],
        'node@api.admin.users/update' => [
            'class' => 'app\\controller\\api\\admin\\Users::update',
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
        'login' => [
            'pid' => '__ROOT__',
            'name' => 'login',
            'sort' => 0,
            'desc' => '用户登录后授予的权限',
            'allow' => [
                'node@admin.main/index',
                'node@admin.main/sysinfo',
                'node@api.admin.auth/userinfo',
                'node@api.system/sysinfo',
            ],
        ],
        'permission' => [
            'pid' => '__ROOT__',
            'name' => 'permission',
            'sort' => 0,
            'desc' => '权限',
            'allow' => null,
        ],
        'permission.info' => [
            'pid' => 'permission',
            'name' => 'permission.info',
            'sort' => 0,
            'desc' => '查看权限',
            'allow' => [
                'node@api.admin.permission/index',
                'node@api.admin.permission/read',
            ],
        ],
        'permission.scan' => [
            'pid' => 'permission',
            'name' => 'permission.scan',
            'sort' => 0,
            'desc' => '重建权限',
            'allow' => [
                'node@api.admin.permission/scan',
            ],
        ],
        'role' => [
            'pid' => '__ROOT__',
            'name' => 'role',
            'sort' => 0,
            'desc' => '角色',
            'allow' => null,
        ],
        'role.del' => [
            'pid' => 'role',
            'name' => 'role.del',
            'sort' => 3,
            'desc' => '删除角色',
            'allow' => [
                'node@api.admin.roles/delete',
            ],
        ],
        'role.edit' => [
            'pid' => 'role',
            'name' => 'role.edit',
            'sort' => 2,
            'desc' => '编辑角色',
            'allow' => [
                'node@api.admin.roles/save',
                'node@api.admin.roles/update',
            ],
        ],
        'role.info' => [
            'pid' => 'role',
            'name' => 'role.info',
            'sort' => 1,
            'desc' => '查看角色',
            'allow' => [
                'node@api.admin.roles/index',
                'node@api.admin.roles/select',
                'node@api.admin.roles/read',
            ],
        ],
        'user' => [
            'pid' => '__ROOT__',
            'name' => 'user',
            'sort' => 0,
            'desc' => '用户',
            'allow' => null,
        ],
        'user.del' => [
            'pid' => 'user',
            'name' => 'user.del',
            'sort' => 4,
            'desc' => '删除用户',
            'allow' => [
                'node@api.admin.users/update',
            ],
        ],
        'user.edit' => [
            'pid' => 'user',
            'name' => 'user.edit',
            'sort' => 2,
            'desc' => '编辑角色',
            'allow' => [
                'node@api.admin.users/save',
            ],
        ],
        'user.info' => [
            'pid' => 'user',
            'name' => 'user.info',
            'sort' => 1,
            'desc' => '查看角色',
            'allow' => [
                'node@api.admin.users/index',
                'node@api.admin.users/read',
            ],
        ],
    ],
    'permission2features' => [
        'login' => [
            'node@admin.main/index',
            'node@admin.main/sysinfo',
            'node@api.admin.auth/userinfo',
            'node@api.system/sysinfo',
        ],
        'permission' => [],
        'permission.info' => [
            'node@api.admin.permission/index',
            'node@api.admin.permission/read',
        ],
        'permission.scan' => [
            'node@api.admin.permission/scan',
        ],
        'role' => [],
        'role.del' => [
            'node@api.admin.roles/delete',
        ],
        'role.edit' => [
            'node@api.admin.roles/save',
            'node@api.admin.roles/update',
        ],
        'role.info' => [
            'node@api.admin.roles/index',
            'node@api.admin.roles/select',
            'node@api.admin.roles/read',
        ],
        'user' => [],
        'user.del' => [
            'node@api.admin.users/update',
        ],
        'user.edit' => [
            'node@api.admin.users/save',
        ],
        'user.info' => [
            'node@api.admin.users/index',
            'node@api.admin.users/read',
        ],
    ],
    'features2permission' => [
        'node@admin.main/index' => 'login',
        'node@admin.main/sysinfo' => 'login',
        'node@api.admin.auth/userinfo' => 'login',
        'node@api.system/sysinfo' => 'login',
        'node@api.admin.permission/index' => 'permission.info',
        'node@api.admin.permission/read' => 'permission.info',
        'node@api.admin.permission/scan' => 'permission.scan',
        'node@api.admin.roles/delete' => 'role.del',
        'node@api.admin.roles/save' => 'role.edit',
        'node@api.admin.roles/update' => 'role.edit',
        'node@api.admin.roles/index' => 'role.info',
        'node@api.admin.roles/select' => 'role.info',
        'node@api.admin.roles/read' => 'role.info',
        'node@api.admin.users/update' => 'user.del',
        'node@api.admin.users/save' => 'user.edit',
        'node@api.admin.users/index' => 'user.info',
        'node@api.admin.users/read' => 'user.info',
    ],
];
