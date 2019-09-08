<?php
/**
 * Created by PhpStorm.
 * User: NHZEXG
 * Date: 2018/12/19
 * Time: 11:31
 */
const SYSTEM_NAME = 'nxAdmin';
const RESOURCE_VERSION = '1.0';
const CSRF_TOKEN = 'XSRF-Token';

// 项目根目录
define('ROOT_PATH', realpath(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
// 系统运行存储
define('STORAGE_PATH', ROOT_PATH . 'storage' . DIRECTORY_SEPARATOR);
// 公开访问文件夹
define('PUBILC_PATH', ROOT_PATH . 'public' . DIRECTORY_SEPARATOR);

// CERTIFICATE 文件路径
define('CA_ROOT_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'runtime' . DIRECTORY_SEPARATOR . 'cacert.pem');
define('CA_ROOT_CHECKSUM_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR . 'cacert.pem.sha256');
