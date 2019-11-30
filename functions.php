<?php
# 优先级最高, 请谨慎使用助手函数

use think\facade\Env;

if (!function_exists('env')) {
    /**
     * 获取环境变量值
     * @access public
     * @param string $name    环境变量名（支持二级 .号分割）
     * @param string $default 默认值
     * @return mixed
     */
    function env(string $name = null, $default = null)
    {
        return Env::get($name, $default);
    }
}
