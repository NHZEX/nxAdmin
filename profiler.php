<?php

use Xhgui\Profiler\Profiler;

require_once __DIR__ . '/vendor/perftools/php-profiler/autoload.php';

$profiler = new Profiler([
    'save.handler'        => Profiler::SAVER_STACK,
    'save.handler.stack'  => [
        'savers'  => [
            Profiler::SAVER_UPLOAD,
            Profiler::SAVER_FILE,
        ],
        // if saveAll=false, break the chain on successful save
        'saveAll' => false,
    ],
    // subhandler specific configs
    'save.handler.file'   => [
        'filename' => '/tmp/nx.xhgui.data.jsonl',
    ],
    'save.handler.upload' => [
        'url'     => 'http://xhgui.test/run/import',
        'timeout' => 3,
        'token'   => 'token',
    ],
]);
$profiler->start();
