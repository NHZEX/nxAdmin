<?php

use app\Service\Auth\AuthService;
use app\Service\DebugHelper\DebugHelperService;
use app\Service\DeployTool\DeployServer;
use app\Service\Redis\RedisService;

return [
    AuthService::class,
    DeployServer::class,
    DebugHelperService::class,
    RedisService::class,
];
