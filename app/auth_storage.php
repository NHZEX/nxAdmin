<?php
// update date: 2020-02-19T23:32:42+08:00
// hash: faea72856644b63ebbceed9af2b4df9c
return [
    'features' => [
        'node@admin.main/userinfo' => [
            'class' => 'app\\controller\\admin\\Main::userInfo',
            'policy' => '',
            'desc' => '',
        ],
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
        'node@admin.main/clearcache' => [
            'class' => 'app\\controller\\admin\\Main::clearCache',
            'policy' => 'userType:admin',
            'desc' => '',
        ],
        'node@admin.manager/index' => [
            'class' => 'app\\controller\\admin\\Manager::index',
            'policy' => '',
            'desc' => '',
        ],
        'node@admin.manager/table' => [
            'class' => 'app\\controller\\admin\\Manager::table',
            'policy' => '',
            'desc' => '',
        ],
        'node@admin.manager/pageedit' => [
            'class' => 'app\\controller\\admin\\Manager::pageEdit',
            'policy' => '',
            'desc' => '',
        ],
        'node@admin.manager/changepassword' => [
            'class' => 'app\\controller\\admin\\Manager::changePassword',
            'policy' => '',
            'desc' => '',
        ],
        'node@admin.manager/save' => [
            'class' => 'app\\controller\\admin\\Manager::save',
            'policy' => '',
            'desc' => '',
        ],
        'node@admin.manager/delete' => [
            'class' => 'app\\controller\\admin\\Manager::delete',
            'policy' => '',
            'desc' => '',
        ],
        'node@admin.menu/index' => [
            'class' => 'app\\controller\\admin\\Menu::index',
            'policy' => '',
            'desc' => '',
        ],
        'node@admin.menu/table' => [
            'class' => 'app\\controller\\admin\\Menu::table',
            'policy' => '',
            'desc' => '',
        ],
        'node@admin.menu/edit' => [
            'class' => 'app\\controller\\admin\\Menu::edit',
            'policy' => '',
            'desc' => '',
        ],
        'node@admin.menu/save' => [
            'class' => 'app\\controller\\admin\\Menu::save',
            'policy' => '',
            'desc' => '',
        ],
        'node@admin.menu/export' => [
            'class' => 'app\\controller\\admin\\Menu::export',
            'policy' => '',
            'desc' => '',
        ],
        'node@admin.menu/delete' => [
            'class' => 'app\\controller\\admin\\Menu::delete',
            'policy' => '',
            'desc' => '',
        ],
        'node@admin.permission/index' => [
            'class' => 'app\\controller\\admin\\Permission::index',
            'policy' => '',
            'desc' => '',
        ],
        'node@admin.permission/permissiontree' => [
            'class' => 'app\\controller\\admin\\Permission::permissionTree',
            'policy' => '',
            'desc' => '',
        ],
        'node@admin.permission/get' => [
            'class' => 'app\\controller\\admin\\Permission::get',
            'policy' => '',
            'desc' => '',
        ],
        'node@admin.permission/save' => [
            'class' => 'app\\controller\\admin\\Permission::save',
            'policy' => '',
            'desc' => '',
        ],
        'node@admin.permission/del' => [
            'class' => 'app\\controller\\admin\\Permission::del',
            'policy' => '',
            'desc' => '',
        ],
        'node@admin.permission/scan' => [
            'class' => 'app\\controller\\admin\\Permission::scan',
            'policy' => '',
            'desc' => '',
        ],
        'node@admin.permission/lasting' => [
            'class' => 'app\\controller\\admin\\Permission::lasting',
            'policy' => '',
            'desc' => '',
        ],
        'node@admin.role/index' => [
            'class' => 'app\\controller\\admin\\Role::index',
            'policy' => '',
            'desc' => '',
        ],
        'node@admin.role/table' => [
            'class' => 'app\\controller\\admin\\Role::table',
            'policy' => '',
            'desc' => '',
        ],
        'node@admin.role/pageedit' => [
            'class' => 'app\\controller\\admin\\Role::pageEdit',
            'policy' => '',
            'desc' => '',
        ],
        'node@admin.role/save' => [
            'class' => 'app\\controller\\admin\\Role::save',
            'policy' => '',
            'desc' => '',
        ],
        'node@admin.role/permission' => [
            'class' => 'app\\controller\\admin\\Role::permission',
            'policy' => '',
            'desc' => '',
        ],
        'node@admin.role/savepermission' => [
            'class' => 'app\\controller\\admin\\Role::savePermission',
            'policy' => '',
            'desc' => '',
        ],
        'node@admin.role/delete' => [
            'class' => 'app\\controller\\admin\\Role::delete',
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
                'node@admin.main/userinfo',
                'node@admin.main/index',
                'node@admin.main/sysinfo',
                'node@admin.main/clearcache',
            ],
        ],
        'menu' => [
            'pid' => '__ROOT__',
            'name' => 'menu',
            'sort' => 0,
            'desc' => '',
            'allow' => null,
        ],
        'menu.del' => [
            'pid' => 'menu',
            'name' => 'menu.del',
            'sort' => 0,
            'desc' => '',
            'allow' => [
                'node@admin.menu/delete',
            ],
        ],
        'menu.edit' => [
            'pid' => 'menu',
            'name' => 'menu.edit',
            'sort' => 0,
            'desc' => '',
            'allow' => [
                'node@admin.menu/save',
            ],
        ],
        'menu.export' => [
            'pid' => 'menu',
            'name' => 'menu.export',
            'sort' => 0,
            'desc' => '',
            'allow' => [
                'node@admin.menu/export',
            ],
        ],
        'menu.info' => [
            'pid' => 'menu',
            'name' => 'menu.info',
            'sort' => 0,
            'desc' => '',
            'allow' => [
                'node@admin.menu/index',
                'node@admin.menu/table',
                'node@admin.menu/edit',
            ],
        ],
        'permission' => [
            'pid' => '__ROOT__',
            'name' => 'permission',
            'sort' => 0,
            'desc' => '权限',
            'allow' => null,
        ],
        'permission.del' => [
            'pid' => 'permission',
            'name' => 'permission.del',
            'sort' => 0,
            'desc' => '',
            'allow' => [
                'node@admin.permission/del',
            ],
        ],
        'permission.edit' => [
            'pid' => 'permission',
            'name' => 'permission.edit',
            'sort' => 0,
            'desc' => '编辑权限',
            'allow' => [
                'node@admin.permission/save',
            ],
        ],
        'permission.info' => [
            'pid' => 'permission',
            'name' => 'permission.info',
            'sort' => 0,
            'desc' => '查看权限',
            'allow' => [
                'node@admin.permission/index',
                'node@admin.permission/permissiontree',
                'node@admin.permission/get',
            ],
        ],
        'permission.lasting' => [
            'pid' => 'permission',
            'name' => 'permission.lasting',
            'sort' => 0,
            'desc' => '',
            'allow' => [
                'node@admin.permission/lasting',
            ],
        ],
        'permission.scan' => [
            'pid' => 'permission',
            'name' => 'permission.scan',
            'sort' => 0,
            'desc' => '重建权限',
            'allow' => [
                'node@admin.permission/scan',
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
                'node@admin.role/delete',
            ],
        ],
        'role.edit' => [
            'pid' => 'role',
            'name' => 'role.edit',
            'sort' => 2,
            'desc' => '编辑角色',
            'allow' => [
                'node@admin.role/save',
                'node@admin.role/savepermission',
            ],
        ],
        'role.info' => [
            'pid' => 'role',
            'name' => 'role.info',
            'sort' => 1,
            'desc' => '查看角色',
            'allow' => [
                'node@admin.role/index',
                'node@admin.role/table',
                'node@admin.role/pageedit',
                'node@admin.role/permission',
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
                'node@admin.manager/delete',
            ],
        ],
        'user.edit' => [
            'pid' => 'user',
            'name' => 'user.edit',
            'sort' => 2,
            'desc' => '编辑角色',
            'allow' => [
                'node@admin.manager/save',
            ],
        ],
        'user.info' => [
            'pid' => 'user',
            'name' => 'user.info',
            'sort' => 1,
            'desc' => '查看角色',
            'allow' => [
                'node@admin.manager/index',
                'node@admin.manager/table',
                'node@admin.manager/pageedit',
            ],
        ],
        'user.password' => [
            'pid' => 'user',
            'name' => 'user.password',
            'sort' => 3,
            'desc' => '更改密码',
            'allow' => [
                'node@admin.manager/changepassword',
            ],
        ],
    ],
    'permission2features' => [
        'login' => [
            'node@admin.main/userinfo',
            'node@admin.main/index',
            'node@admin.main/sysinfo',
            'node@admin.main/clearcache',
        ],
        'menu' => [],
        'menu.del' => [
            'node@admin.menu/delete',
        ],
        'menu.edit' => [
            'node@admin.menu/save',
        ],
        'menu.export' => [
            'node@admin.menu/export',
        ],
        'menu.info' => [
            'node@admin.menu/index',
            'node@admin.menu/table',
            'node@admin.menu/edit',
        ],
        'permission' => [],
        'permission.del' => [
            'node@admin.permission/del',
        ],
        'permission.edit' => [
            'node@admin.permission/save',
        ],
        'permission.info' => [
            'node@admin.permission/index',
            'node@admin.permission/permissiontree',
            'node@admin.permission/get',
        ],
        'permission.lasting' => [
            'node@admin.permission/lasting',
        ],
        'permission.scan' => [
            'node@admin.permission/scan',
        ],
        'role' => [],
        'role.del' => [
            'node@admin.role/delete',
        ],
        'role.edit' => [
            'node@admin.role/save',
            'node@admin.role/savepermission',
        ],
        'role.info' => [
            'node@admin.role/index',
            'node@admin.role/table',
            'node@admin.role/pageedit',
            'node@admin.role/permission',
        ],
        'user' => [],
        'user.del' => [
            'node@admin.manager/delete',
        ],
        'user.edit' => [
            'node@admin.manager/save',
        ],
        'user.info' => [
            'node@admin.manager/index',
            'node@admin.manager/table',
            'node@admin.manager/pageedit',
        ],
        'user.password' => [
            'node@admin.manager/changepassword',
        ],
    ],
    'features2permission' => [
        'node@admin.main/userinfo' => 'login',
        'node@admin.main/index' => 'login',
        'node@admin.main/sysinfo' => 'login',
        'node@admin.main/clearcache' => 'login',
        'node@admin.menu/delete' => 'menu.del',
        'node@admin.menu/save' => 'menu.edit',
        'node@admin.menu/export' => 'menu.export',
        'node@admin.menu/index' => 'menu.info',
        'node@admin.menu/table' => 'menu.info',
        'node@admin.menu/edit' => 'menu.info',
        'node@admin.permission/del' => 'permission.del',
        'node@admin.permission/save' => 'permission.edit',
        'node@admin.permission/index' => 'permission.info',
        'node@admin.permission/permissiontree' => 'permission.info',
        'node@admin.permission/get' => 'permission.info',
        'node@admin.permission/lasting' => 'permission.lasting',
        'node@admin.permission/scan' => 'permission.scan',
        'node@admin.role/delete' => 'role.del',
        'node@admin.role/save' => 'role.edit',
        'node@admin.role/savepermission' => 'role.edit',
        'node@admin.role/index' => 'role.info',
        'node@admin.role/table' => 'role.info',
        'node@admin.role/pageedit' => 'role.info',
        'node@admin.role/permission' => 'role.info',
        'node@admin.manager/delete' => 'user.del',
        'node@admin.manager/save' => 'user.edit',
        'node@admin.manager/index' => 'user.info',
        'node@admin.manager/table' => 'user.info',
        'node@admin.manager/pageedit' => 'user.info',
        'node@admin.manager/changepassword' => 'user.password',
    ],
];
