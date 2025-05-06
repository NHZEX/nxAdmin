<?php

declare(strict_types=1);

namespace Tp\Model;

use function end;

class Event
{
    private array $event = [];

    /**
     * 注册回调方法.
     *
     * @param string $event 事件名
     * @param callable $callback 回调方法
     */
    public function listen(string $event, callable $callback): void
    {
        $this->event[$event][] = $callback;
    }

    /**
     * 触发事件.
     *
     * @param string $event 事件名
     * @param mixed $params 传入参数
     * @param bool $once 只获取一个有效返回值
     */
    public function trigger(string $event, $params = null, bool $once = false)
    {
        $result = [];
        if (isset($this->event[$event])) {
            foreach ($this->event[$event] as $key => $callback) {
                $result[$key] = \call_user_func($callback, $params);
                if (false === $result[$key] || (null !== $result[$key] && $once)) {
                    break;
                }
            }
        }

        return $once ? end($result) : $result;
    }

    public function get(): array
    {
        return $this->event;
    }

    public function clear(): void
    {
        $this->event = [];
    }
}
