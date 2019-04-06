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

namespace app\dirver\session;

use think\facade\Log;
use think\session\driver\Redis;

class Redis2 extends Redis
{
    /**
     * 读取Session
     * @access public
     * @param  string $sessID
     * @return string
     */
    public function read($sessID): string
    {
        $sessKey = $this->config['session_name'] . $sessID;
        Log::record($this->handler->ping(), 'session');
        Log::record('read_sees: ' . $sessKey, 'session');
        Log::record('read_result: ' . $this->handler->get($sessKey), 'session');
        $result = $this->handler->get($sessKey);
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
        $sessKey = $this->config['session_name'] . $sessID;
        Log::record($this->handler->ping(), 'session');
        Log::record('write_sees: ' . $sessKey, 'session');
        Log::record('write_result: ' . $sessData, 'session');
        Log::save();
        if ($this->config['expire'] > 0) {
            $result = $this->handler->setex($sessKey, $this->config['expire'], $sessData);
        } else {
            $result = $this->handler->set($sessKey, $sessData);
        }
        return $result ? true : false;
    }

    /**
     * 删除Session
     * @access public
     * @param string $sessID
     * @return bool
     */
    public function destroy($sessID): bool
    {
        $sessKey = $this->config['session_name'] . $sessID;
        return !$this->handler->exists($sessKey) || $this->handler->delete($sessKey) > 0;
    }
}
