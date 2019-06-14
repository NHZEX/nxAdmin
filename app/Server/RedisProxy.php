<?php
/**
 * Created by PhpStorm.
 * User: NHZEXG
 * Date: 2018/7/21
 * Time: 17:52
 */

namespace app\Server;

use app\Facade\Redis;
use Redis\RedisExtend;
use Smf\ConnectionPool\BorrowConnectionTimeoutException;
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

    /** @var RedisExtend */
    protected $handler2 = null;

    public function __construct(Config $config)
    {
        $this->config = $config->get('redis') + $this->config;
    }

    public function setConfig(array $cfg, $reconnect = false)
    {
        $this->config = $cfg + $this->config;
    }

    /**
     * @return bool
     */
    protected function boot()
    {
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     * @throws BorrowConnectionTimeoutException
     */
    public function __call($name, $arguments)
    {
        /** @var ConnectionPool $pools */
        $pools = app()->make(ConnectionPool::class);
        $poredis = $pools->getConnectionPool('redis');
        /** @var \Redis $redis */
        $redis = $poredis->borrow();
        $result = $redis->$name(...$arguments);
        $poredis->return($redis);
        return $result;
    }

    /**
     * @return RedisExtend|null|false
     * @author NHZEXG
     * @deprecated
     */
    public static function getInstance()
    {
        return Redis::instance();
    }
}
