<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------
// | 模板设置
// +----------------------------------------------------------------------

use nhzex\Blade\Blade\Driver as BladeDriver;
use think\facade\Env;

return [
    // 模板引擎类型 支持 php think 支持扩展
    'type'         => BladeDriver::class,
    // 默认模板渲染规则 1 解析为小写+下划线 2 全部转换小写 3 保持操作方法
    'auto_rule'    => 1,
    // 视图基础目录（集中式）
    'view_base'    => \think\facade\App::getAppPath() . 'view' . DIRECTORY_SEPARATOR,
    // 模板路径
    'view_path'    => '',
    // 模板后缀
    'view_suffix'  => 'blade.php',
    // 模板文件名分隔符
    'view_depr'    => DIRECTORY_SEPARATOR,
    // 模板引擎普通标签开始标记
    'tpl_begin'    => '{',
    // 模板引擎普通标签结束标记
    'tpl_end'      => '}',
    // 标签库标签开始标记
    'taglib_begin' => '{',
    // 标签库标签结束标记
    'taglib_end'   => '}',
    // 是否开启模板编译缓存,设为false则每次都会重新编译
    'tpl_cache'    => boolval(Env::get('app.debug') ? Env::get('app.tpl_cache', true) : true),
];
