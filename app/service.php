<?php

use app\Service\DebugHelper\DebugHelperService;
use app\Service\DeployTool\DeployServer;
use HZEX\Think\Cors\Service as CorsService;
use Zxin\Think\Auth\Service as AuthService;

return [
    CorsService::class,
    AuthService::class,
    DeployServer::class,
    DebugHelperService::class,
    Zxin\Think\Validate\ValidateService::class,
    //ValidateService::class,
];
