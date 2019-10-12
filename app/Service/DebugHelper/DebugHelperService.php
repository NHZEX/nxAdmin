<?php
declare(strict_types=1);

namespace app\Service\DebugHelper;

use app\Service\DebugHelper\Middleware\DebugRequestInfo;
use think\Service;

/**
 * 调试助手服务
 * Class DebugHelperService
 * @package app\Service\DebugHelper
 */
class DebugHelperService extends Service
{
    public function register()
    {
        if ($this->app->isDebug()) {
            if (extension_loaded('xdebug')) {
                ini_set('xdebug.var_display_max_depth', '10');
                ini_set('xdebug.var_display_max_children', '256');
                ini_set('xdebug.var_display_max_data', '1024');
            }
            $this->app->middleware->add(DebugRequestInfo::class, 'controller');
        }
    }

    public function boot()
    {
    }
}
