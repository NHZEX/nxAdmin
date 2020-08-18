<?php
declare(strict_types=1);

namespace app\Service\Swoole;

use think\App;
use think\Container;
use think\swoole\contract\ResetterInterface;
use think\swoole\Sandbox;

class ResetDb implements ResetterInterface
{

    /**
     * "handle" function for resetting app.
     *
     * @param Container|App         $app
     * @param \think\swoole\Sandbox $sandbox
     */
    public function handle(Container $app, Sandbox $sandbox): void
    {
        if ($app->exists('db')) {
            $app->db->setLog($app->log);
        }
    }
}
