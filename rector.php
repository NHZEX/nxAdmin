<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/app',
        __DIR__ . '/config',
        __DIR__ . '/extend',
        __DIR__ . '/route',
    ])
    ->withBootstrapFiles([
        __DIR__ . '/app/common.php',
        __DIR__ . '/vendor/autoload.php',
    ])
    ->withSkip([
        __DIR__ . '/app/auth_storage.php',
        __DIR__ . '/app/route_storage.dump.php',
        __DIR__ . '/app/validate_storage.php',
    ])
    ->withPreparedSets(deadCode: true)
    // ->withImportNames(importShortClasses: false)
    ->withPhpSets()
    ->withPHPStanConfigs([
        __DIR__ . '/phpstan.neon',
    ]);
