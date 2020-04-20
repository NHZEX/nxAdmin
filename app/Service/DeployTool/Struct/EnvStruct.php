<?php

namespace app\Service\DeployTool\Struct;

use HZEX\DataStruct\BaseProperty;
use think\facade\Env;

class EnvStruct extends BaseProperty
{
    public static function read(
        $prefixs = ['DB_', 'REDIS_', 'CACHE_', 'LOG_', 'SESSION_', 'SERVER_']
    ) {
        $preg = join('|', $prefixs);
        $preg = "/^({$preg})/";

        $that = new self();
        $data = Env::get();

        foreach ($data as $key => $value) {
            if (isset($that->$key)
                || preg_match($preg, $key)
            ) {
                $that->$key = $value;
            }
        }

        return $that;
    }

    public $APP_DEBUG = 0;

    public $SYSTEM_WEB_TITLE = SYSTEM_NAME;

    public $LOG_CHANNEL = 'file';
    public $LOG_FILE_PATH = '';
    public $LOG_FILE_MAX_FILES = 30;
    public $LOG_FILE_FILE_SIZE = 4194304;
    public $LOG_REMOTE_HOST = '127.0.0.1';
    public $LOG_REMOTE_FORCE_CLIENT = 'develop';
    public $LOG_REMOTE_ALLOW_CLIENT = 'develop';

    public $REDIS_HOST = '127.0.0.1';
    public $REDIS_PORT = 6379;
    public $REDIS_PASSWORD = '';
    public $REDIS_SELECT = 0;
    public $REDIS_TIMEOUT = 3;
    public $REDIS_PERSISTENT = 0;

    public $DEPLOY_SECURITY_SALT = '';
    public $DEPLOY_ROOT_PATH_SIGN = '';
    public $DEPLOY_MIXING_PREFIX = '';

    public $TASK_USER = '';

    public $DEVELOP_SECURE_DOMAIN_NAME = '';
}
