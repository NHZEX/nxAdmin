<?php
declare(strict_types=1);

namespace Tp\Log;

use function in_array;
use function is_string;
use function strlen;

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
        $this->log[$type][] = $msg;

        if (!$this->lazy || !$lazy) {
            $this->save();
        }

        return $this;
    }
}
