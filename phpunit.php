#!/usr/bin/env php
<?php

require_once __DIR__ . '/functions.php';

const PHPUNIT_ENTRANCE = __DIR__ . '/vendor/phpunit/phpunit/phpunit';
const PHPUNIT_ENTRANCE_COPY = __DIR__ . '/vendor/phpunit/phpunit/phpunit.copy';

$script = file_get_contents(PHPUNIT_ENTRANCE);
file_put_contents(PHPUNIT_ENTRANCE_COPY, substr($script, strpos($script, '<?php')));

/** @noinspection PhpIncludeInspection */
require_once PHPUNIT_ENTRANCE_COPY;
