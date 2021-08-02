<?php

use app\Service\Auth\Record\RecordService;
use app\Service\DebugHelper\DebugHelperService;
use app\Service\DeployTool\DeployServer;
use app\Service\Swoole\SwooleService;
use HZEX\Think\Cors\Service as CorsService;
use Zxin\Think\Auth\Service as AuthService;
use Zxin\Think\Validate\ValidateService;

return [
    SwooleService::class,
    CorsService::class,
    AuthService::class,
    RecordService::class,
    DeployServer::class,
    DebugHelperService::class,
    ValidateService::class,
];
