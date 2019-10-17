<?php
/**
 * Created by PhpStorm.
 * User: NHZEXG
 * Date: 2019/1/28
 * Time: 14:26
 */

namespace app\Service\Redis;

use Redis;

/**
 * Class RedisExtend
 * @package app\Service\Redis
 * TODO 处理 evalSha 错误问题
 */
class RedisExtend extends Redis
{
    private const SCRIPT_SERIAL_INC = <<<'LUA'
local sno = redis.call('INCR', KEYS[1])
if sno > 65535 then
    sno = 1
    redis.call('SET', KEYS[1], 1)
end
redis.call('EXPIRE', KEYS[1], 1800)
return sno
LUA;

    private const SCRIPT_RELEASE_LOCK = <<<'LUA'
if ARGV[1] == redis.call('GET', KEYS[1]) then
    return redis.call('DEL', KEYS[1]) or true
end
return false
LUA;

    private $lua_sha1 = [
        'serial_inc' => null,
        'release_lock' => null,
    ];

    /**
     * 初始化LUA脚本
     * @author NHZEXG
     */
    public function initScript()
    {
        $this->lua_sha1['serial_inc'] = $this->script('load', self::SCRIPT_SERIAL_INC);
        $this->lua_sha1['release_lock'] = $this->script('load', self::SCRIPT_RELEASE_LOCK);
    }

    /**
     * 查询LUA是否存在
     * @author NHZEXG
     */
    public function existLuaSerialInc()
    {
        $this->script('EXISTS', $this->lua_sha1['serial_inc']);
    }

    /**
     * 序列号自增
     * @param string $serial
     * @author NHZEXG
     * @return int
     */
    public function serialInc(string $serial)
    {
        return $this->evalSha($this->lua_sha1['serial_inc'], ["__serial_number:{$serial}"], 1);
    }

    /**
     * 获得锁
     * @param string $name
     * @param int $retry_timeout
     * @param int $lock_timeout
     * @author NHZEXG
     * @return bool|string
     */
    public function acquireLock(string $name, int $retry_timeout = 0, int $lock_timeout = 1000)
    {
        $lock_name = "__lock:{$name}";
        $lock_id = uuidv4();

        $end = (int) (microtime(true) * 1000) + $retry_timeout;
        do {
            if (empty($lock_timeout)) {
                $result = $this->set($lock_name, $lock_id, ['NX']);
            } else {
                $result = $this->set($lock_name, $lock_id, ['NX', 'PX' => $lock_timeout]);
            }
            if ($result) {
                break;
            }
            usleep(1000);
        } while (!$result && (int) (microtime(true) * 1000) < $end);

        return $result ? $lock_id : $result;
    }

    /**
     * 释放锁
     * @param string $name
     * @param string $lock_id
     * @param bool $force
     * @author NHZEXG
     * @return int|mixed
     */
    public function releaseLock(string $name, string $lock_id, $force = false)
    {
        $lock_name = "__lock:{$name}";
        if ($force) {
            return $this->del($lock_name);
        } else {
            return $this->evalSha($this->lua_sha1['release_lock'], [$lock_name, $lock_id], 1);
        }
    }

    /**
     * 值相等并删除
     * @param string $name
     * @param string $lock_id
     * @author NHZEXG
     * @return int|mixed
     */
    public function valueEqualWithDel(string $name, string $lock_id)
    {
        $result = $this->evalSha($this->lua_sha1['release_lock'], [$name, $lock_id], 1);
        return (bool) $result;
    }

    public function getServerVersion() :string
    {
        /** @var array $redis_info */
        $redis_info = $this->info('SERVER');
        return $redis_info['redis_version'];
    }
}
