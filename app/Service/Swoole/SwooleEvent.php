<?php
declare(strict_types=1);

namespace app\Service\Swoole;

use Closure;
use RuntimeException;
use think\App;
use think\Event;
use function env;
use function file_exists;
use function unlink;

class SwooleEvent
{
    protected $app;

    public function __construct(App $app)
    {
        $this->app = $app;
    }


    /**
     * @param callable $call
     * @return Closure
     */
    public static function callWrap(callable $call)
    {
        return Closure::fromCallable($call);
    }


    public function subscribe(Event $event)
    {
        $event->listen('swoole.init', self::callWrap([$this, 'init']));
    }

    protected function init()
    {
        $server = SwooleService::getServer();
        $unixsock = env('SERV_HTTP_UNIXSOCK');
        if ($unixsock) {
            if (file_exists($unixsock)) {
                if (!unlink($unixsock)) {
                    throw new RuntimeException('unix sock existed: ' . $unixsock);
                }
            }
            $server->addlistener($unixsock, 0, SWOOLE_UNIX_STREAM);
        }
    }
}
