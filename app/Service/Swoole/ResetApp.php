<?php
declare(strict_types=1);

namespace app\Service\Swoole;

use ReflectionException;
use ReflectionObject;
use think\App;
use think\Container;
use think\swoole\contract\ResetterInterface;
use think\swoole\Sandbox;

class ResetApp implements ResetterInterface
{

    /**
     * "handle" function for resetting app.
     *
     * @param Container|App $app
     * @param Sandbox   $sandbox
     */
    public function handle(Container $app, Sandbox $sandbox): void
    {
        // 重置应用的开始时间和内存占用
        try {
            $ref = new ReflectionObject($app);
            $refBeginTime = $ref->getProperty('beginTime');
            $refBeginTime->setAccessible(true);
            $refBeginTime->setValue($app, microtime(true));
            $refBeginMem = $ref->getProperty('beginMem');
            $refBeginMem->setAccessible(true);
            $refBeginMem->setValue($app, memory_get_usage());
        } catch (ReflectionException $e) {
        }
    }
}
