<?php

namespace app\Controller\api;

use Captcha\Captcha;
use think\App;
use think\Response;
use Util\Reply;
use Zxin\Think\Auth\Annotation\Auth;
use function ini_get;
use function php_uname;
use function realpath_cache_size;
use const PHP_SAPI;
use const PHP_VERSION;

class System extends Base
{
    /**
     * 基本系统设置
     */
    public function config()
    {
        return Reply::success([
            'webTitle' => env('SYSTEM_WEB_TITLE'),
            'loginCaptcha' => config('captcha.login'),
        ]);
    }

    /**
     * @Auth()
     * @return Response
     */
    public function sysinfo()
    {
        return Reply::success([
            'sys_version' => ['服务器系统', php_uname()],
            'server_software' => ['执行环境', $_SERVER['SERVER_SOFTWARE']],
            'php_sapi' => ['PHP接口类型', PHP_SAPI],
            'tp_version' => ['ThinkPHP 版本', App::VERSION],
            'php_version' => ['PHP版本', PHP_VERSION],
            'db_version' => ['数据库版本', db_version(null, true)],
            'memory_limit' => ['内存限制', ini_get('memory_limit')],
            'max_execution_time' => ['最长执行时间', ini_get('max_execution_time')],
            'upload_max_filesize' => ['上传限制', ini_get('upload_max_filesize')],
            'post_max_size' => ['POST限制', ini_get('post_max_size')],
            'realpath_cache_size' => ['路径缓存', realpath_cache_size()],
            'preload_statistics' => ['预加载', preload_statistics()]
        ]);
    }

    /**
     * 获取一个验证码
     * @param Captcha $captcha
     * @return Response
     */
    public function captcha(Captcha $captcha)
    {
        $captcha->entry();
        return $captcha->send()->header([
            'X-Captcha-Token' => $captcha->generateToken(),
        ]);
    }
}
