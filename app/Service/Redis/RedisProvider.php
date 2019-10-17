<?php
/**
 * Created by PhpStorm.
 * User: NHZEXG
 * Date: 2018/7/21
 * Time: 17:52
 */

namespace app\Service\Redis;

use RedisException;
use think\Config;

/**
 * Class RedisProxy
 * @package app\server
 * @mixin RedisExtend
 */
class RedisProvider
{
    protected $init = false;

    /** @var array 配置 */
    protected $config = [
        'host' => '127.0.0.1',
        'port' => '6379',
        'password' => '',
        'select' => 0,
        'timeout' => 1,
        'persistent' => 1,
    ];

    /** @var RedisExtend */
    protected $handler2 = null;

    public function __construct(Config $config)
    {
        $this->config = $config->get('redis') + $this->config;
    }

    public function setConfig(array $cfg, $reconnect = false)
    {
        $this->config = $cfg + $this->config;
        if ($reconnect && $this->handler2 instanceof RedisExtend) {
            $this->handler2->close();
            $this->handler2 = null;
            $this->init = false;
        }
    }

    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @return bool
     * @throws RedisException
     */
    protected function boot()
    {
        $this->handler2 = new RedisExtend();

        if ($this->config['persistent']) {
            $result = $this->handler2->pconnect($this->config['host'], $this->config['port'], $this->config['timeout']);
        } else {
            $result = $this->handler2->connect($this->config['host'], $this->config['port'], $this->config['timeout']);
        }
        if (false === $result) {
            return false;
        }

        if (!empty($this->config['password'])) {
            $this->handler2->auth($this->config['password']);
        }

        $result = '+PONG' === $this->handler2->ping();
        if (false === $result) {
            return false;
        }

        if (0 != $this->config['select']) {
            $result = $this->handler2->select($this->config['select']);
        }
        if (false === $result) {
            return false;
        } else {
            $this->handler2->initScript();
            return true;
        }
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     * @throws RedisException
     */
    public function __call($name, $arguments)
    {
        if (false === $this->init) {
            $this->init = $this->boot();
        }
        return $this->handler2->$name(...$arguments);
    }
}
