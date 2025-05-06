<?php

namespace app\Traits\Model;

use think\App;
use think\db\exception\ModelEventException;
use think\Event;
use function end;
use function method_exists;

/**
 * Trait ModelEvent.
 *
 * @mixin \think\model\concern\ModelEvent
 */
trait ModelEvent
{
    /**
     * 事件监听.
     */
    public static function listen(string $event, callable $call, bool $first = false): void
    {
        App::getInstance()->make('model.event')->listen('model.'.static::class.'.'.$event, $call, $first);
    }

    /**
     * 事件触发.
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
                if (false === \call_user_func([$this, $callMethod], $this)) {
                    return false;
                }
            }
            if (self::$event instanceof Event) {
                $result = App::getInstance()
                    ->make('model.event')
                    ->trigger('model.'.static::class.'.'.$event, $this);
                $result = empty($result) ? true : end($result);
                if (false === $result) {
                    return false;
                }
            }

            return true;
        } catch (ModelEventException) {
            return false;
        }
    }
}
