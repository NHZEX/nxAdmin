<?php


namespace app\Facade;

use Mlog\Logger as LoggerServer;

/**
 * Class Redis
 * @package app\Facade
 * @mixin LoggerServer
 * @method LoggerServer instance() static
 * @method void log($level, $message, array $context = array())
 * @method void debug($message, array $context = array()) static
 * @method void info($message, array $context = array()) static
 * @method void notice($message, array $context = array()) static
 * @method void warning($message, array $context = array()) static
 * @method void error($message, array $context = array()) static
 * @method void critical($message, array $context = array()) static
 * @method void alert($message, array $context = array()) static
 * @method void emergency($message, array $context = array()) static
 */
class Logger extends Base
{
    protected static function getFacadeClass()
    {
        return LoggerServer::class;
    }
}
