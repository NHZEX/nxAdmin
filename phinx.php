<?php
/** @noinspection PhpUnhandledExceptionInspection */
return
[
    'paths' => [
        'migrations' => '%%PHINX_CONFIG_DIR%%/phinx/migrations',
        'seeds' => '%%PHINX_CONFIG_DIR%%/phinx/seeds'
    ],
    'environments' => [
        'default_migration_table' => '_phinxlog',
        'default_database' => 'development',
        'development' => [
            'connection' => \think\Db::connect()->getConnection()->connect(),
            'name' => \think\Db::getConfig('database'),
        ],
        'example' => [
            'adapter' => 'mysql',
            'host' => 'localhost',
            'name' => 'production_db',
            'user' => 'root',
            'pass' => '',
            'port' => '3306',
            'charset' => 'utf8',
        ],
    ],
    'version_order' => 'creation'
];
