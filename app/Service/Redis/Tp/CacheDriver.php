<?php

namespace app\Service\Redis\Tp;

use app\Facade\Redis;
use app\Service\Redis\Connections\PhpRedisConnection;
use think\cache\Driver;

class CacheDriver extends Driver
{
    /**
     * 驱动句柄
     * @var PhpRedisConnection
     */
    protected $handler = null;

    /**
     * 配置参数
     * @var array
     */
    protected $options = [
        'connection' => null,
        'expire'     => 0,
        'prefix'     => '',
        'tag_prefix' => 'tag:',
        'serialize'  => [],
    ];

    public function __construct(array $options = [])
    {
        if (!empty($options)) {
            $this->options = array_merge($this->options, $options);
        }
        if (empty($this->options['connection'])) {
            $this->options['connection'] = null;
        }
        $this->handler = Redis::connection($this->options['connection']);
    }

    public function inc(string $name, int $step = 1)
    {
        $this->writeTimes++;

        $key = $this->getCacheKey($name);

        return $this->handler->incrby($key, $step);
    }

    public function dec(string $name, int $step = 1)
    {
        $this->writeTimes++;

        $key = $this->getCacheKey($name);

        return $this->handler->decrby($key, $step);
    }

    public function clearTag(array $keys)
    {
        // 指定标签清除
        $this->handler->del($keys);
    }

    public function get($key, $default = null)
    {
        $this->readTimes++;

        $value = $this->handler->get($this->getCacheKey($key));

        if (false === $value || is_null($value)) {
            return $default;
        }

        return $this->unserialize($value);
    }

    public function set($key, $value, $ttl = null)
    {
        $this->writeTimes++;

        if (is_null($ttl)) {
            $ttl = $this->options['expire'];
        }

        $key    = $this->getCacheKey($key);
        $expire = $this->getExpireTime($ttl);
        $value  = $this->serialize($value);

        if ($expire) {
            $this->handler->setex($key, $expire, $value);
        } else {
            $this->handler->set($key, $value);
        }

        return true;
    }

    public function delete($key)
    {
        $this->writeTimes++;

        $result = $this->handler->del($this->getCacheKey($key));
        return $result > 0;
    }

    public function clear()
    {
        $this->writeTimes++;

        $this->handler->flushDB();
        return true;
    }

    public function has($key)
    {
        return $this->handler->exists($this->getCacheKey($key)) ? true : false;
    }

    /**
     * 追加（数组）缓存数据
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
     * @param string $tag 缓存标签
     * @return array
     */
    public function getTagItems(string $tag): array
    {
        return $this->handler->sMembers($tag);
    }
}
