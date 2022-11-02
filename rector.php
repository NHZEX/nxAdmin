<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php73\Rector\FuncCall\JsonThrowOnErrorRector;
use Rector\Php74\Rector\LNumber\AddLiteralSeparatorToNumberRector;
use Rector\Set\ValueObject\LevelSetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/app',
        __DIR__ . '/config',
        __DIR__ . '/extend',
        __DIR__ . '/route',
    ]);
    $rectorConfig->bootstrapFiles([
        __DIR__ . '/app/common.php',
        __DIR__ . '/vendor/autoload.php',
    ]);

    $rectorConfig->skip([
        __DIR__ . '/app/auth_storage.php',
        __DIR__ . '/app/validate_storage.php',
        JsonThrowOnErrorRector::class,
        AddLiteralSeparatorToNumberRector::class,
    ]);

    $rectorConfig->rules([]);

    // define sets of rules
    $rectorConfig->sets([
        LevelSetList::UP_TO_PHP_74,
    ]);

    $rectorConfig->phpstanConfig(__DIR__ . '/phpstan.neon');
};
