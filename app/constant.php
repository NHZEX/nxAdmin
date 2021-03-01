<?php

const SYSTEM_NAME = 'nxAdmin';
const RESOURCE_VERSION = '1.0';
const CSRF_TOKEN = 'XSRF-Token';

// 项目根目录
\define('ROOT_PATH', \realpath(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
// 系统运行存储
\define('STORAGE_PATH', ROOT_PATH . 'storage' . DIRECTORY_SEPARATOR);
// 公开访问文件夹
\define('PUBLIC_PATH', ROOT_PATH . 'public' . DIRECTORY_SEPARATOR);

const ROUTE_DEFAULT_RESTFULL = [
    'index'  => ['get', '', 'index'],
    'select' => ['get', '/select', 'select'],
    'read'   => ['get', '/<id>', 'read'],
    'save'   => ['post', '', 'save'],
    'update' => ['put', '/<id>', 'update'],
//    'patch'  => ['patch', '/<id>', 'patch'],
    'delete' => ['delete', '/<id>', 'delete'],
];
