<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\Php73\Rector\FuncCall\JsonThrowOnErrorRector;
use Rector\Php74\Rector\LNumber\AddLiteralSeparatorToNumberRector;
use Rector\Set\ValueObject\LevelSetList;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    // get parameters
    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::PATHS, [
        __DIR__ . '/app',
        __DIR__ . '/config',
        __DIR__ . '/extend',
        __DIR__ . '/route',
    ]);

    $parameters->set(Option::BOOTSTRAP_FILES, [
        __DIR__ . '/app/common.php',
        __DIR__ . '/vendor/autoload.php',
    ]);

    $parameters->set(Option::SKIP, [
        __DIR__ . '/app/auth_storage.php',
        __DIR__ . '/app/validate_storage.php',
        JsonThrowOnErrorRector::class,
        AddLiteralSeparatorToNumberRector::class,
    ]);

     // $parameters->set(Option::AUTO_IMPORT_NAMES, true);
     // $parameters->set(Option::IMPORT_SHORT_CLASSES, false);

    // Define what rule sets will be applied
    $containerConfigurator->import(LevelSetList::UP_TO_PHP_74);
};
