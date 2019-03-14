<?php
/**
 * Created by PhpStorm.
 * User: NHZEXG
 * Date: 2018/12/19
 * Time: 11:31
 */

const RESOURCE_VERSION = '1.0';
const CSRF_TOKEN = 'XSRF-Token';
const API_TOKEN_V1 = 0x01;

// 常用协议头
const HEADER_X9_APPID = 'X9-Appid';
const HEADER_X9_TIMESTAMP = 'X9-Timestamp';
const HEADER_X9_NONCE = 'X9-Nonce';
const HEADER_X9_SIGNATURE = 'X9-Signature';
const HEADER_X9_TOKEN = 'X9-Token';
const HEADER_USER_AGENT = 'user-agent';
const HEADER_X9_PACKAGE_NAME = 'package-name';
const HEADER_X9_PACKAGE_VERSION = 'package-version';

$env_root_path = think\facade\Env::get('root_path');
$env_runtime_path = think\facade\Env::get('runtime_path');
// 系统运行存储
define('STORAGE_PATH', $env_root_path . 'storage' . DIRECTORY_SEPARATOR);
// 公开访问文件夹
define('PUBILC_PATH', $env_root_path . 'public' . DIRECTORY_SEPARATOR);
// 上传文件存储

//上传文件存储路径
const UPLOAD_STORAGE_PATH = PUBILC_PATH . 'upload' . DIRECTORY_SEPARATOR;

//上传文件访问路径
const UPLOAD_ACCESS_PATH = DIRECTORY_SEPARATOR . 'upload' . DIRECTORY_SEPARATOR;

//上传Apk路径
const UPLOAD_APK_PATH = UPLOAD_STORAGE_PATH . 'apks' . DIRECTORY_SEPARATOR;

//ui文件目录
const UI_CONFIG_STORAGE = PUBILC_PATH . 'storage' . DIRECTORY_SEPARATOR;
//自定义ui文件目录
const UI_CONFIG_CUSTOM = UI_CONFIG_STORAGE . 'custom' . DIRECTORY_SEPARATOR;
//默认桌面ui文件目录
const UI_DESKTOP_DEFAULT = UI_CONFIG_STORAGE . 'desktop_ui' . DIRECTORY_SEPARATOR;
//默认商店ui文件目录
const UI_STORE_DEFAULT = UI_CONFIG_STORAGE . 'store_ui' . DIRECTORY_SEPARATOR;



// CERTIFICATE 文件路径
define('CA_ROOT_PATH', $env_runtime_path . 'cacert.pem');
define('CA_ROOT_CHECKSUM_PATH', $env_runtime_path . 'cacert.pem.sha256');
