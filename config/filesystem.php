<?php

// 目前已经弃用

return [
    // 默认磁盘
    'default' => \env('filesystem.driver', 'local'),
    // 磁盘列表
    'disks'   => [
        'upload'  => [
            'type' => 'local',
            'root' => app()->getRootPath() . 'public/upload',
        ],
        'local'  => [
            'type' => 'local',
            'root' => app()->getRuntimePath() . 'storage',
        ],
        'public' => [
            // 磁盘类型
            'type'       => 'local',
            // 磁盘路径
            'root'       => app()->getRootPath() . 'public/storage',
            // 磁盘路径对应的外部URL路径
            'url'        => '/storage',
            // 可见性
            'visibility' => 'public',
        ],
        // 更多的磁盘配置信息
    ],
];
