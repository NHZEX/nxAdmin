<?php

namespace Mlog\Handler;

use Monolog\Handler\AbstractProcessingHandler;

abstract class Base extends AbstractProcessingHandler
{
    abstract public function save();
}
