<?php
/**
 * Created by PhpStorm.
 * User: NHZEXG
 * Date: 2018/7/21
 * Time: 17:52
 */

namespace app\Server;

use Co;
use HZEX\TpSwoole\Worker\ConnectionPool;
use Redis\RedisExtend;
use Smf\ConnectionPool\BorrowConnectionTimeoutException;
use Smf\ConnectionPool\ConnectionPool as SmfConnectionPool;
use think\Config;

/**
 * Class RedisProxy
 * @package app\server
 * @mixin RedisExtend
 */
class RedisProxy
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

    protected $isSwoole = false;

    /** @var string */
    protected $poolName;

    /** @var SmfConnectionPool */
    protected $pools;

    /** @var RedisExtend */
    protected $handler2 = null;

    public function __construct(Config $config)
    {
        $this->config = $config->get('redis') + $this->config;
        $this->isSwoole = exist_swoole();
    }

    public function setConfig(array $cfg, $reconnect = false)
    {
        $this->config = $cfg + $this->config;
        if ($reconnect && $this->init) {
            $this->__destruct();
        }
    }

    /**
     * @return bool
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
            return true;
        }
    }

    /**
     * @throws BorrowConnectionTimeoutException
     */
    protected function bootPool()
    {
        /** @var ConnectionPool $pools */
        $pools = app()->make(ConnectionPool::class);
        $this->pools = $pools->requestRedis([
            'host'     => $this->config['host'],
            'port'     => $this->config['port'],
            'database' => $this->config['select'],
            'password' => $this->config['password'],
            'timeout'  => $this->config['timeout'],
        ], $this->poolName);

        $this->handler2 = $this->pools->borrow();

        return true;
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     * @throws BorrowConnectionTimeoutException
     */
    public function __call($name, $arguments)
    {
        if (false === $this->init) {
            if (false === $this->isSwoole || -1 === Co::getCid()) {
                $this->init = $this->boot();
            } else {
                $this->init = $this->bootPool();
            }
        }

        return $this->handler2->$name(...$arguments);
    }

    /**
     * 关闭连接
     */
    public function closeLink()
    {
        if ($this->init) {
            if (false === $this->isSwoole || -1 === Co::getCid()) {
                $this->handler2->close();
            } else {
                $this->pools->return($this->handler2);
            }
        }
        $this->handler2 = null;
        $this->init = false;
    }

    public function __destruct()
    {
        $this->closeLink();
    }
}
