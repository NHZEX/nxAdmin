<?php

namespace app\Service\Redis;

use app\Service\Redis\Connections\PhpRedisConnection;
use RuntimeException;
use think\Config;

/**
 * Class RedisProxy
 * @package app\server
 */
class RedisProvider
{
    /** @var array 配置 */
    protected $config = [];

    /**
     * The Redis connections.
     *
     * @var PhpRedisConnection[]
     */
    protected $connections;

    public function __construct(Config $config)
    {
        $this->config = $config->get('redis') + $this->config;
    }

    /**
     * @param null $name
     * @return PhpRedisConnection
     */
    public function connection($name = null)
    {
        $default = $name ?? $this->config['default'];
        if (!isset($this->config['connections'][$default])) {
            throw new RuntimeException("invalid connection: {$default}");
        }

        if (!isset($this->connections[$default])) {
            $this->connections[$default] = new PhpRedisConnection($this->config['connections'][$default]);
        }

        return $this->connections[$default];
    }

    public function destroy($name = null)
    {
        $default = $name ?? $this->config['default'];
        if (!isset($this->config['connections'][$default])) {
            throw new RuntimeException("invalid connection: {$default}");
        }

        unset($this->connections[$default]);
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        return $this->connection()->command($name, $arguments);
    }
}
