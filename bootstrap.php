<?php

namespace think;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'functions.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

if (defined('PHPUNIT_COMPOSER_INSTALL')) {
    $app = App::getInstance();
    if (!$app->initialized()) {
        $app->initialize();
    }
}
