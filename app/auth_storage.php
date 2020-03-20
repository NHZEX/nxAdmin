<?php
// update date: 2020-03-20T14:12:22+08:00
// hash: c0db4ec6d7acce4e78180acd795c5554
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
                'node@api.admin.index/userinfo',
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
                'node@api.admin.role/delete',
            ],
        ],
        'role.edit' => [
            'pid' => 'role',
            'name' => 'role.edit',
            'sort' => 2,
            'desc' => '编辑角色',
            'allow' => [
                'node@api.admin.role/save',
                'node@api.admin.role/update',
            ],
        ],
        'role.info' => [
            'pid' => 'role',
            'name' => 'role.info',
            'sort' => 1,
            'desc' => '查看角色',
            'allow' => [
                'node@api.admin.role/index',
                'node@api.admin.role/select',
                'node@api.admin.role/read',
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
                'node@api.admin.user/update',
            ],
        ],
        'user.edit' => [
            'pid' => 'user',
            'name' => 'user.edit',
            'sort' => 2,
            'desc' => '编辑角色',
            'allow' => [
                'node@api.admin.user/save',
            ],
        ],
        'user.info' => [
            'pid' => 'user',
            'name' => 'user.info',
            'sort' => 1,
            'desc' => '查看角色',
            'allow' => [
                'node@api.admin.user/index',
                'node@api.admin.user/read',
            ],
        ],
    ],
    'permission2features' => [
        'login' => [
            'node@admin.main/index',
            'node@admin.main/sysinfo',
            'node@api.admin.index/userinfo',
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
            'node@api.admin.role/delete',
        ],
        'role.edit' => [
            'node@api.admin.role/save',
            'node@api.admin.role/update',
        ],
        'role.info' => [
            'node@api.admin.role/index',
            'node@api.admin.role/select',
            'node@api.admin.role/read',
        ],
        'user' => [],
        'user.del' => [
            'node@api.admin.user/update',
        ],
        'user.edit' => [
            'node@api.admin.user/save',
        ],
        'user.info' => [
            'node@api.admin.user/index',
            'node@api.admin.user/read',
        ],
    ],
    'features2permission' => [
        'node@admin.main/index' => 'login',
        'node@admin.main/sysinfo' => 'login',
        'node@api.admin.index/userinfo' => 'login',
        'node@api.system/sysinfo' => 'login',
        'node@api.admin.permission/index' => 'permission.info',
        'node@api.admin.permission/read' => 'permission.info',
        'node@api.admin.permission/scan' => 'permission.scan',
        'node@api.admin.role/delete' => 'role.del',
        'node@api.admin.role/save' => 'role.edit',
        'node@api.admin.role/update' => 'role.edit',
        'node@api.admin.role/index' => 'role.info',
        'node@api.admin.role/select' => 'role.info',
        'node@api.admin.role/read' => 'role.info',
        'node@api.admin.user/update' => 'user.del',
        'node@api.admin.user/save' => 'user.edit',
        'node@api.admin.user/index' => 'user.info',
        'node@api.admin.user/read' => 'user.info',
    ],
];
