<?php
/**
 * Created by PhpStorm.
 * User: NHZEXG
 * Date: 2019/1/28
 * Time: 10:35
 */

namespace app\Struct;

use HZEX\DataStruct\BaseProperty;
use think\facade\Env;

class EnvStruct extends BaseProperty
{
    public static function read()
    {
        $that = new self();
        $keys = array_keys($that->toArray());

        foreach ($keys as $key) {
            $that->$key = Env::get($key, $that->$key);
        }

        return $that;
    }

    public $APP_DEBUG = 0;
    public $APP_TRACE = 0;
    public $APP_TPL_CACHE = 1;

    public $DEVELOP_SECURE_DOMAIN_NAME = '';

    public $SYSTEM_LOG_FILE_PATH = '';
    public $SYSTEM_WEB_TITLE = 'nxAdmin';

    public $REMOTELOG_ENABLE = 0;
    public $REMOTELOG_HOST = '127.0.0.1';
    public $REMOTELOG_FORCE_CLIENT_ID = 'develop';

    public $DATABASE_HOSTNAME = '127.0.0.1';
    public $DATABASE_HOSTPORT = 3306;
    public $DATABASE_DATABASE = 'base';
    public $DATABASE_USERNAME = 'root';
    public $DATABASE_PASSWORD = '';
    public $DATABASE_DEBUG = 0;
    public $DATABASE_SQL_EXPLAIN = 0;

    public $REDIS_HOST = '127.0.0.1';
    public $REDIS_PORT = 6379;
    public $REDIS_PASSWORD = '';
    public $REDIS_SELECT = 0;
    public $REDIS_TIMEOUT = 3;
    public $REDIS_PERSISTENT = 0;

    public $TASK_USER = '';

    public $DEPLOY_SECURITY_SALT = '';
    public $DEPLOY_ROOT_PATH_SIGN = '';
    public $DEPLOY_MIXING_PREFIX = '';
}
