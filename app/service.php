<?php

use app\Service\DebugHelper\DebugHelperService;
use app\Service\DeployTool\DeployServer;
use HZEX\Think\Cors\Service as CorsService;
use Zxin\Think\Auth\Record\RecordService;
use Zxin\Think\Auth\Service as AuthService;
use Zxin\Think\Route\RouteService;
use Zxin\Think\Validate\ValidateService;

return [
    RouteService::class,
    CorsService::class,
    AuthService::class,
    RecordService::class,
    DeployServer::class,
    DebugHelperService::class,
    ValidateService::class,
];
