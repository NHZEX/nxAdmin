<?php
/**
 * Created by PhpStorm.
 * User: NHZEXG
 * Date: 2019/1/28
 * Time: 10:35
 */

namespace app\struct;

use HZEX\DataStruct\BaseProperty;

class EnvStruct extends BaseProperty
{
    public $app = [
        'debug' => 0,
        'trace' => 0,
        'tpl_cache' => 1,
    ];

    public $develop = [
        'secure_domain_name' => '',
    ];

    public $system = [
        'log_file_path' => '',
        'web_title' => 'nxAdmin',
    ];

    public $remotelog = [
        'enable' => 0,
        'host' => '127.0.0.1',
        'force_client_id' => 'develop',
    ];

    public $database = [
        'hostname' => '127.0.0.1',
        'hostport' => 3306,
        'database' => 'base',
        'username' => 'root',
        'password' => '',
        'debug' => 0,
        'sql_explain' => 0,
    ];

    public $redis = [
        'host' => '127.0.0.1',
        'port' => 6379,
        'password' => '',
        'select' => 0,
        'timeout' => 3,
        'persistent' => 0,
    ];

    public $task = [
        'user' => '',
    ];
}
