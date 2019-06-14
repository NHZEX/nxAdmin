<?php


namespace Mlog;

use Mlog\Handler\RotatingFileHandler;
use Mlog\Handler\SocketLogHandler;
use think\App;
use think\facade\Env;

class Logger extends \Monolog\Logger
{
    public function __construct(App $app)
    {
        $handlers = [];

        //SocketLog远程日志
        if (Env::get('remotelog.enable', false)) {
            $socketLogHandler = new SocketLogHandler($app);
            $handlers[] = $socketLogHandler;
        }

        //文件日志
        $date = date('Ym', time());
        $filename = $app->getRuntimePath() . 'log' . DIRECTORY_SEPARATOR . $date . DIRECTORY_SEPARATOR . 'log.log';
        $rotatingFileHandler = new RotatingFileHandler($filename);
        $handlers[] = $rotatingFileHandler;

        parent::__construct('my_log', $handlers);
    }

    public function save(): bool
    {
        foreach ($this->handlers as $handler) {
            if ($handler instanceof SocketLogHandler) {
                $handler->save();
            }
        }
        return true;
    }
}
