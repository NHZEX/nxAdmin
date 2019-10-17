<?php

use HZEX\SimpleRpc\Transfer\Instance\RpcFacadeClass;

/**
 * Class RpcTest
 * @method index(string $str, int $time)
 */
class RpcTest extends RpcFacadeClass
{
    /**
     * @return string
     */
    protected function getFacadeClass(): string
    {
        return 'test';
    }
}
