<?php
/**
 * Created by PhpStorm.
 * User: NHZEXG
 * Date: 2018/10/27
 * Time: 16:29
 */

namespace app\server;

use basis\Util;
use think\facade\Env;

/**
 * Class Deploy
 * @package app\common\server
 *
 * @method static getSecuritySalt()
 * @method static getRootPathSign()
 * @method static getMixingPrefix()
 */
class Deploy
{
    const ITEM_NAME = 'deploy';

    /**
     * 生成部署配置
     */
    public static function init()
    {
        $security_salt = get_rand_str(32);
        $root_path_sign = dechex(crc32(Env::get('root_path') . 'dir'));
        $mixing_prefix = $root_path_sign.'_'.dechex(crc32($security_salt));

        $env_contents = [
            'security_salt' => $security_salt,
            'root_path_sign' => $root_path_sign,
            'mixing_prefix' => $mixing_prefix,
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
        if (0 === strpos($name, 'get')) {
            return Env::get(Deploy::ITEM_NAME . '.' . Util::toSnakeCase(substr($name, 3)));
        }

        throw new \RuntimeException('Fatal error: Call to undefined method '.__CLASS__."::{$name}");
    }
}
