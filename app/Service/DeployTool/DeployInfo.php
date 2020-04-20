<?php

namespace app\Service\DeployTool;

use think\App;

class DeployInfo
{
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

        return [
            'DEPLOY_SECURITY_SALT' => $security_salt,
            'DEPLOY_ROOT_PATH_SIGN' => $root_path_sign,
            'DEPLOY_MIXING_PREFIX' => $mixing_prefix,
        ];
    }
}
