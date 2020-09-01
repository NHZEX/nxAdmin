<?php

namespace app\controller\api;

use Captcha\Captcha;
use think\App;
use think\Response;
use Zxin\Think\Auth\Annotation\Auth;
use function func\reply\reply_succeed;
use function get_server_software;

class System extends Base
{
    /**
     * 基本系统设置
     */
    public function config()
    {
        return reply_succeed([
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
        return reply_succeed([
            'sys_version' => ['服务器系统', php_uname()],
            'server_software' => ['执行环境', get_server_software()],
            'php_sapi' => ['PHP接口类型', php_sapi_name()],
            'tp_version' => ['ThinkPHP 版本', App::VERSION],
            'php_version' => ['PHP版本', phpversion()],
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
