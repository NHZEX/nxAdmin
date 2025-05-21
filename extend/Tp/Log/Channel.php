<?php

declare(strict_types=1);

namespace Tp\Log;

use Composer\InstalledVersions;
use think\event\LogRecord;
use function strtr;

class Channel extends \think\log\Channel
{
    private static ?bool $newImplement = null;

    private static function isNewImplement(): bool
    {
        if (null !== self::$newImplement) {
            return self::$newImplement;
        }

        $version = ltrim(InstalledVersions::getPrettyVersion('topthink/framework'), 'v');
        if (preg_match('~^(\d+\.?)+$~', $version)) {
            $newImplement = (bool) version_compare($version, '8.1.2', '>');
        } else {
            $newImplement = !class_exists('\think\log\driver\Socket');
        }
        return self::$newImplement = $newImplement;
    }

    public function record($msg, string $type = 'info', array $context = [], bool $lazy = true)
    {
        if ($this->close || (!empty($this->allow) && !\in_array($type, $this->allow))) {
            return $this;
        }

        if ($msg instanceof \Stringable) {
            $msg = (string) $msg;
        }

        if (\is_string($msg) && !empty($context)) {
            $replace = [];
            foreach ($context as $key => $val) {
                $replace['{'.$key.'}'] = is_string($val) ? $val : var_export($val, true);
            }

            $msg = strtr($msg, $replace);
        }

        if ('' === $msg) {
            $msg = '"(empty string)"';
        } elseif (null === $msg) {
            $msg = '"(null)"';
        }

        if (!empty($msg) || 0 === $msg) {
            if (self::isNewImplement()) {
                $this->log[] = [$type, $msg];
            } else {
                $this->log[$type][] = $msg;
            }
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
