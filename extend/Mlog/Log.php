<?php

namespace Mlog;

use Mlog\Handler\FileHandler;
use Mlog\Handler\SocketLogHandler;
use Monolog\Handler\BufferHandler;
use Monolog\Logger;
use think\App;
use think\facade\Env;

class Log extends \think\Log
{
    protected $logger;

    /**
     * Log constructor.
     * @param App $app
     * @throws \Exception
     */
    public function __construct(App $app)
    {
        parent::__construct($app);

        $handlers = [];
        //SocketLog远程日志
        if (Env::get('remotelog.enable', false)) {
            $socketLogHandler = new BufferHandler(new SocketLogHandler($app));
            $handlers[] = $socketLogHandler;
        }

        $date = date('Ym', time());
        $filename = $app->getRuntimePath() . 'log' . DIRECTORY_SEPARATOR . $date . DIRECTORY_SEPARATOR . 'log.log';
        $rotatingFileHandler = new FileHandler('my_log', $filename, Logger::WARNING);
        $handler = new BufferHandler($rotatingFileHandler);
        $handlers[] = $handler;

        $this->logger = new Logger('my_log', $handlers);
    }

    public function log($level, $message, array $context = []): void
    {
        $this->logger->log($level, $message, $context);
    }

    public function debug($message, array $context = []): void
    {
        $this->logger->debug($message, $context);
    }

    public function info($message, array $context = []): void
    {
        $this->logger->info($message, $context);
    }

    public function notice($message, array $context = []): void
    {
        $this->logger->notice($message, $context);
    }

    public function warning($message, array $context = []): void
    {
        $this->logger->warning($message, $context);
    }

    public function error($message, array $context = []): void
    {
        $this->logger->error($message, $context);
    }

    public function critical($message, array $context = []): void
    {
        $this->logger->critical($message, $context);
    }

    public function alert($message, array $context = []): void
    {
        $this->logger->alert($message, $context);
    }

    public function emergency($message, array $context = []): void
    {
        $this->logger->emergency($message, $context);
    }

    public function save(): bool
    {
        return parent::save();
    }
}
