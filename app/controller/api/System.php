<?php

namespace app\controller\api;

use app\Service\Auth\Annotation\Auth;
use Captcha\Captcha;
use think\App;
use think\Response;

class System extends Base
{
    /**
     * 基本系统设置
     */
    public function config()
    {
        return self::showJson([
            'webTitle' => env('SYSTEM_WEB_TITLE'),
            'loginCaptcha' => config('captcha.login'),
        ]);
    }

    /**
     * @Auth()
     * @param App $app
     * @return Response
     */
    public function sysinfo(App $app)
    {
        return self::showJson([
            'cms_version' => ['CMS 系统版本', '1.0.0'],
            'tp_version' => ['ThinkPHP 版本', $app->version()],
            'sys_version' => ['服务器系统', php_uname()],
            'server_software' => ['执行环境', $_SERVER['SERVER_SOFTWARE']],
            'php_version' => ['PHP版本', phpversion()],
            'php_sapi' => ['PHP接口类型', php_sapi_name()],
            'mysql_version' => ['MySQL版本', query_mysql_version()],
            'memory_limit' => ['内存限制', ini_get('memory_limit')],
            'max_execution_time' => ['最长执行时间', ini_get('max_execution_time')],
            'upload_max_filesize' => ['上传限制', ini_get('upload_max_filesize')],
            'post_max_size' => ['POST限制', ini_get('post_max_size')],
            'realpath_cache_size' => ['路径缓存', realpath_cache_size()],
        ]);
    }

    /**
     * 获取一个验证码
     * @param Captcha $captcha
     * @param string  $token
     * @return Response
     */
    public function captcha(Captcha $captcha, string $token = null)
    {
        if (!$token) {
            return self::showCode(400);
        }

        $captcha->entry();
        $captcha->saveToRedis($token);
        return $captcha->send();
    }
}
