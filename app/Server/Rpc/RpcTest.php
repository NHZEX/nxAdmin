<?php
declare(strict_types=1);

namespace app\Server\Rpc;

use think\App;

class RpcTest
{
    public function index(string $str, int $time): int
    {
        App::getInstance();
        dump(__METHOD__ . ": {$str}($time)");

        return mt_rand();
    }
}
