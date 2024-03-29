<?php

declare(strict_types=1);

namespace Tp\Log;

use think\event\LogRecord;
use function in_array;
use function is_string;
use function strlen;
use function strtr;

class Channel extends \think\log\Channel
{
    public function record($msg, string $type = 'info', array $context = [], bool $lazy = true)
    {
        if ($this->close || (!empty($this->allow) && !in_array($type, $this->allow))) {
            return $this;
        }

        if (is_string($msg) && !empty($context)) {
            $replace = [];
            foreach ($context as $key => $val) {
                $replace['{' . $key . '}'] = $val;
            }

            $msg = strtr($msg, $replace);
        }

        if (is_string($msg) && strlen($msg) === 0) {
            $msg = '"(empty string)"';
        }

        if (!empty($msg) || 0 === $msg) {
            $this->log[$type][] = $msg;
            if ($this->event) {
                $this->event->trigger(new LogRecord($type, $msg));
            }
        }

        if (!$this->lazy || !$lazy) {
            $this->save();
        }

        return $this;
    }
}
