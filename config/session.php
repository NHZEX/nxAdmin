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
// | 会话设置
// +----------------------------------------------------------------------

use app\Server\DeployInfo;
use think\session\driver\Cache;

return [
    // SESSION COOKIN
    'name'           => 'one',
    // SESSION_ID的提交变量,解决flash上传跨域
    'var_session_id' => '',
    // 驱动方式 支持 file cache
    'type'           => Cache::class,
    // 存储连接标识 当type使用cache的时候有效
    'store'          => 'session',
    // SESSION 超时(2小时)
    'expire'         => (int) env_get('SESSION_EXPIRE', 7200),
    // Memcached And Redis SESSION KEY 的前缀
    'prefix'   => DeployInfo::getMixingPrefix() . ':sess:',
];
