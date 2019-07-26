<?php
declare(strict_types=1);

namespace Tp\Cache\Driver;

use app\Service\Redis\RedisProvider;
use Closure;
use Co;
use DateTime;
use Exception;
use think\cache\Driver;
use throwable;

/**
 * Class Redis
 * @package Tp\Session\Driver
 */
class Redis extends Driver
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
    protected $handler;

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
        if (null === $this->handler) {
            $this->handler = \app\Facade\Redis::newMake([], true);
            $this->handler->setConfig([
                'host' => $this->options['host'],
                'port' => $this->options['port'],
                'password' => $this->options['password'],
                'select' => $this->options['select'],
                'timeout' => $this->options['timeout'],
                'persistent' => $this->options['persistent'],
            ], true);
            \app\Facade\Redis::addStoresHosting($this->handler);
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
        return 1 == $this->handler->exists($this->getCacheKey($name));
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

        $value = $this->handler->get($this->getCacheKey($name));

        if (is_null($value) || false === $value) {
            return $default;
        }

        return $this->unserialize($value);
    }

    /**
     * 写入缓存
     * @access public
     * @param  string           $name   缓存变量名
     * @param  mixed            $value  存储数据
     * @param  integer|DateTime $expire 有效时间（秒）
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
            $result = $this->handler->setex($key, $expire, $value);
        } else {
            $result = $this->handler->set($key, $value);
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

        return $this->handler->incrby($key, $step);
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

        return $this->handler->decrby($key, $step);
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

        $this->handler->del($this->getCacheKey($name));
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

        $this->handler->flushDB();
        return true;
    }

    /**
     * 删除缓存标签
     * @access public
     * @param array $keys 缓存标识列表
     * @return void
     */
    public function clearTag(array $keys): void
    {
        // 指定标签清除
        $this->handler->del($keys);
    }

    /**
     * 追加（数组）缓存数据
     * @access public
     * @param string $name  缓存标识
     * @param mixed  $value 数据
     * @return void
     */
    public function push(string $name, $value): void
    {
        $this->handler->sAdd($name, $value);
    }

    /**
     * 获取标签包含的缓存标识
     * @access public
     * @param string $tag 缓存标签
     * @return array
     */
    public function getTagItems(string $tag): array
    {
        return $this->handler->sMembers($tag);
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
                $value = $value();
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
