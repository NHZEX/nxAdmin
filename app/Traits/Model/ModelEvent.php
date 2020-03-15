<?php

namespace app\Traits\Model;

use think\App;
use think\db\exception\ModelEventException;
use think\Event;

/**
 * Trait ModelEvent
 * @package app\Traits\Model
 * @mixin \think\model\concern\ModelEvent
 */
trait ModelEvent
{
    /**
     * 事件触发
     * @param string   $event
     * @param callable $call
     * @param bool     $first
     * @return Event
     */
    public static function listen(string $event, callable $call, bool $first = false)
    {
        return App::getInstance()->event->listen(static::class . '.' . $event, $call, $first);
    }

    /**
     * 事件触发
     * @param string $event
     * @return bool
     */
    protected function trigger(string $event): bool
    {
        if (!$this->withEvent) {
            return true;
        }

        // 允许模型事件方法与事件管理器全部触发
        try {
            // method_exists 忽略大小写，不需要做驼峰转换
            $callMethod = "on{$event}";
            if (method_exists($this, $callMethod)) {
                if (($result = call_user_func([$this, $callMethod], $this)) === false) {
                    return false;
                }
            }
            if (self::$event instanceof Event) {
                $result = self::$event->trigger(static::class . '.' . $event, $this);
                $result = empty($result) ? true : end($result);
                if ($result === false) {
                    return false;
                }
            }
            return true;
        } /** @noinspection PhpRedundantCatchClauseInspection */ catch (ModelEventException $e) {
            return false;
        }
    }
}
