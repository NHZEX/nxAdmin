<?php
/**
 * Created by PhpStorm.
 * User: NHZEXG
 * Date: 2018/10/27
 * Time: 16:29
 */

namespace app\Server;

use HZEX\Util;
use RuntimeException;
use think\App;

/**
 * Class Deploy
 * @package app\common\server
 *
 * @method static getSecuritySalt(): string
 * @method static getRootPathSign(): string
 * @method static getMixingPrefix(): string
 */
class DeployInfo
{
    const ITEM_NAME = 'deploy';

    protected static $CAHCE = [];

    /**
     * 生成部署配置
     */
    public static function init()
    {
        $app = App::getInstance();

        if ($app->env->get('DEPLOY_SECURITY_SALT', false)
            && $app->env->get('DEPLOY_ROOT_PATH_SIGN', false)
            && $app->env->get('DEPLOY_MIXING_PREFIX', false)
        ) {
            return [];
        }

        $security_salt = get_rand_str(32);
        $root_path_sign = dechex(crc32($app->getRootPath() . 'dir'));
        $mixing_prefix = $root_path_sign . '_' . dechex(crc32($security_salt));

        $env_contents = [
            'DEPLOY_SECURITY_SALT' => $security_salt,
            'DEPLOY_ROOT_PATH_SIGN' => $root_path_sign,
            'DEPLOY_MIXING_PREFIX' => $mixing_prefix,
        ];

        return $env_contents;
    }

    /**
     * 模式方法获取部署配置
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public static function __callStatic($name, $arguments)
    {
        if (isset(self::$CAHCE[$name])) {
            return self::$CAHCE[$name];
        }

        if (0 === strpos($name, 'get')) {
            $value = App::getInstance()->env->get('DEPLOY_' . Util::toSnakeCase(substr($name, 3)));
            return self::$CAHCE[$name] = $value;
        }

        throw new RuntimeException('Fatal error: Call to undefined method ' . __CLASS__ . "::{$name}");
    }
}
