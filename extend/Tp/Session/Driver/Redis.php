<?php

// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

namespace Tp\Session\Driver;

use app\Service\Redis\RedisProvider;
use think\contract\SessionHandlerInterface;
use think\facade\Log;

/**
 * Class Redis
 * @package Tp\Session\Driver
 * TODO 未完全兼容
 */
class Redis implements SessionHandlerInterface
{
    protected $debug = false;

    /** @var RedisProvider */
    private $redis;

    protected $config  = [
        'host'       => '127.0.0.1', // redis主机
        'port'       => 6379, // redis端口
        'password'   => '', // 密码
        'select'     => 0, // 操作库
        'expire'     => 3600, // 有效期(秒)
        'timeout'    => 0, // 超时时间(秒)
        'persistent' => true, // 是否长连接
        'prefix'     => '', // session key前缀
    ];

    public function __construct(array $config = [])
    {
        $this->config = array_merge($this->config, $config);
        $this->debug = $this->config['debug'] ?? false;
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
                'host' => $this->config['host'],
                'port' => $this->config['port'],
                'password' => $this->config['password'],
                'select' => $this->config['select'],
                'timeout' => $this->config['timeout'],
                'persistent' => $this->config['persistent'],
            ], true);
            \app\Facade\Redis::addStoresHosting($this->redis);
        }
        return true;
    }

    /**
     * 读取Session
     * @access public
     * @param  string $sessID
     * @return string
     */
    public function read($sessID): string
    {
        $sessKey = $this->config['prefix'] . $sessID;

        $result = $this->redis->get($sessKey);
        if ($this->debug) {
            Log::record('read_sees: ' . $sessKey, 'session');
            Log::record('read_result: ' . (empty($result) ? 'is_null' : 'not_null'), 'session');
        }
        return is_string($result) ? $result : '';
    }

    /**
     * 写入Session
     * @access public
     * @param string $sessID
     * @param string $sessData
     * @return bool
     */
    public function write($sessID, $sessData): bool
    {
        if (empty($sessData)) {
            return true;
        }
        $sessKey = $this->config['prefix'] . $sessID;
        if ($this->debug) {
            Log::record('write_sees: ' . $sessKey, 'session');
            Log::record('write_result: ' . (empty($sessData) ? 'is_null' : 'not_null'), 'session');
            Log::save();
        }
        if ($this->config['expire'] > 0) {
            $result = $this->redis->setex($sessKey, $this->config['expire'], $sessData);
        } else {
            $result = $this->redis->set($sessKey, $sessData);
        }
        return $result ? true : false;
    }

    /**
     * 删除Session
     * @access public
     * @param  string $sessID
     * @return bool
     */
    public function delete(string $sessID): bool
    {
        $sessKey = $this->config['prefix'] . $sessID;
        return !$this->redis->exists($sessKey) || $this->redis->delete($sessKey) > 0;
    }
}
