<?php

namespace Preload;

const ROOT_DIR = __DIR__;

require_once ROOT_DIR . '/functions.php';
require_once ROOT_DIR . '/vendor/autoload.php';
require_once ROOT_DIR . '/preload/Preloader.php';

/**
 * opcache.preload = /home/vagrant/code/project/preload.php
 * opcache.preload_user = vagrant
 */

(new Preloader())
    ->paths(
        ROOT_DIR . '/vendor/topthink',
        ROOT_DIR . '/vendor/hashids',
        ROOT_DIR . '/vendor/nhzex',
        ROOT_DIR . '/vendor/rybakit',
        ROOT_DIR . '/vendor/psr',
        ROOT_DIR . '/vendor/doctrine',
        ROOT_DIR . '/vendor/symfony/var-dumper',
        ROOT_DIR . '/vendor/symfony/var-exporter'
    )
    ->ignore()
    ->load();
