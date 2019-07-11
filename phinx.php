<?php

/** @noinspection PhpUnhandledExceptionInspection */
return
[
    'paths' => [
        'migrations' => [
            'DbMigrations' => './phinx/migrations',
        ],
        'seeds' => [
            'DbSeeds' => './phinx/seeds'
        ]
    ],
    'environments' => [
        'default_migration_table' => '_phinxlog',
        'default_database' => 'runtime',
        'runtime' => [
            'connection' => app()->db->connect()->getConnection()->connect(),
            'name' => app()->db->connect()->getConfig('database'),
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
