<?php

use app\Service\Auth\AuthService;
use app\Service\DebugHelper\DebugHelperService;
use app\Service\DeployTool\DeployServer;
use app\Service\Redis\RedisService;
use HZEX\Think\Cors\Service as CorsService;

return [
    CorsService::class,
    AuthService::class,
    DeployServer::class,
    DebugHelperService::class,
    RedisService::class,
];
