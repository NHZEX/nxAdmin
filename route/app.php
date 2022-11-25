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

use think\facade\App;
use think\Response;

$r = App::getInstance()->route;

// 重定义资源路由
$r->rest(ROUTE_DEFAULT_RESTFULL, true);

$r->get('upload', fn() => Response::create('404 Not Found', 'html', 404));
$r->get('static', fn() => Response::create('404 Not Found', 'html', 404));
$r->get('storage', fn() => Response::create('404 Not Found', 'html', 404));

return [];
