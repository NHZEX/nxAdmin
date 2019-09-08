<?php

use app\Service\DebugHelper\DebugHelperService;
use app\Service\DeployTool\DeployServer;
use app\Service\Redis\RedisService;

return [
    DeployServer::class,
    DebugHelperService::class,
    RedisService::class,
];
