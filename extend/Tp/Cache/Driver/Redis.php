<?php
declare(strict_types=1);

namespace Tp\Cache\Driver;

use app\Service\Redis\RedisProvider;
use Closure;
use Co;
use Exception;
use think\cache\Driver;
use think\Container;
use think\contract\CacheHandlerInterface;
use throwable;

/**
 * Class Redis
 * @package Tp\Session\Driver
 * TODO 未完全兼容
 */
class Redis extends Driver implements CacheHandlerInterface
{
    /**
     * 配置参数
     * @var array
     */
    protected $options = [
        'host'       => '127.0.0.1',
        'port'       => 6379,
        'password'   => '',
        'select'     => 0,
        'timeout'    => 0,
        'expire'     => 0,
        'persistent' => false,
        'prefix'     => '',
        'tag_prefix' => 'tag:',
        'serialize'  => [],
    ];

    protected $isSwoole = false;

    /** @var RedisProvider */
    private $redis;

    /**
     * 架构函数
     * @access public
     * @param  array $options 缓存参数
     */
    public function __construct(array $options = [])
    {
        if (!empty($options)) {
            $this->options = array_merge($this->options, $options);
        }

        $this->isSwoole = exist_swoole();
        $this->init();
    }

    /**
     * 打开Session
     * @access protected
     * @return bool
     */
    protected function init(): bool
    {
        if (null === $this->redis) {
            $this->redis = \app\Facade\Redis::newMake([], true);
            $this->redis->setConfig([
                'host' => $this->options['host'],
                'port' => $this->options['port'],
                'password' => $this->options['password'],
                'select' => $this->options['select'],
                'timeout' => $this->options['timeout'],
                'persistent' => $this->options['persistent'],
            ], true);
            \app\Facade\Redis::addStoresHosting($this->redis);
        }
        return true;
    }

    /**
     * 判断缓存
     * @access public
     * @param  string $name 缓存变量名
     * @return bool
     */
    public function has($name): bool
    {
        return 1 == $this->redis->exists($this->getCacheKey($name));
    }

    /**
     * 读取缓存
     * @access public
     * @param  string $name 缓存变量名
     * @param  mixed  $default 默认值
     * @return mixed
     */
    public function get($name, $default = false)
    {
        $this->readTimes++;

        $value = $this->redis->get($this->getCacheKey($name));

        if (is_null($value) || false === $value) {
            return $default;
        }

        return $this->unserialize($value);
    }

    /**
     * 写入缓存
     * @access public
     * @param  string            $name 缓存变量名
     * @param  mixed             $value  存储数据
     * @param  integer|\DateTime $expire  有效时间（秒）
     * @return bool
     */
    public function set($name, $value, $expire = null): bool
    {
        $this->writeTimes++;

        if (is_null($expire)) {
            $expire = $this->options['expire'];
        }

        $key    = $this->getCacheKey($name);
        $expire = $this->getExpireTime($expire);
        $value  = $this->serialize($value);

        if ($expire) {
            $result = $this->redis->setex($key, $expire, $value);
        } else {
            $result = $this->redis->set($key, $value);
        }

        return $result;
    }

    /**
     * 自增缓存（针对数值缓存）
     * @access public
     * @param  string $name 缓存变量名
     * @param  int    $step 步长
     * @return false|int
     */
    public function inc(string $name, int $step = 1)
    {
        $this->writeTimes++;

        $key = $this->getCacheKey($name);

        return $this->redis->incrby($key, $step);
    }

    /**
     * 自减缓存（针对数值缓存）
     * @access public
     * @param  string $name 缓存变量名
     * @param  int    $step 步长
     * @return false|int
     */
    public function dec(string $name, int $step = 1)
    {
        $this->writeTimes++;

        $key = $this->getCacheKey($name);

        return $this->redis->decrby($key, $step);
    }

    /**
     * 删除缓存
     * @access public
     * @param  string $name 缓存变量名
     * @return bool
     */
    public function delete($name): bool
    {
        $this->writeTimes++;

        $this->redis->del($this->getCacheKey($name));
        return true;
    }

    /**
     * 清除缓存
     * @access public
     * @return bool
     */
    public function clear(): bool
    {
        $this->writeTimes++;

        $this->redis->flushDB();
        return true;
    }

    /**
     * 如果不存在则写入缓存
     * @access public
     * @param string $name   缓存变量名
     * @param mixed  $value  存储数据
     * @param int    $expire 有效时间 0为永久
     * @return mixed
     * @throws throwable
     */
    public function remember(string $name, $value, $expire = null)
    {
        if ($this->has($name)) {
            return $this->get($name);
        }

        $time = time();

        while ($time + 5 > time() && $this->has($name . '_lock')) {
            // 存在锁定则等待
            if (!$this->isSwoole || -1 === Co::getCid()) {
                usleep(200000);
            } else {
                Co::sleep(0.02);
            }

        }

        try {
            // 锁定
            $this->set($name . '_lock', true);

            if ($value instanceof Closure) {
                // 获取缓存数据
                $value = Container::getInstance()->invokeFunction($value);
            }

            // 缓存数据
            $this->set($name, $value, $expire);

            // 解锁
            $this->delete($name . '_lock');
        } catch (Exception | throwable $e) {
            $this->delete($name . '_lock');
            throw $e;
        }

        return $value;
    }
}
