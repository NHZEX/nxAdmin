<?php
declare(strict_types=1);

namespace Tp\Model;

class Event
{
    private $event = [];

    /**
     * 注册回调方法
     * @param string   $event    事件名
     * @param callable $callback 回调方法
     * @return void
     */
    public function listen(string $event, callable $callback): void
    {
        $this->event[$event][] = $callback;
    }

    /**
     * 触发事件
     * @param string $event  事件名
     * @param mixed  $params 传入参数
     */
    public function trigger(string $event, $params = null)
    {
        if (isset($this->event[$event])) {
            foreach ($this->event[$event] as $callback) {
                call_user_func($callback, $params);
            }
        }
    }

    /**
     * @return array
     */
    public function get(): array
    {
        return $this->event;
    }

    public function clear()
    {
        $this->event = [];
    }
}
