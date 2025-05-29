<?php

declare(strict_types=1);

namespace Tp\Log;

use Composer\InstalledVersions;
use Stringable;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\AbstractDumper;
use Symfony\Component\VarDumper\Dumper\CliDumper;
use think\event\LogRecord;
use function strtr;

class Channel extends \think\log\Channel
{
    private static ?bool $newImplement = null;

    private VarCloner $cloner;
    private CliDumper $dumper;

    private static function isNewImplement(): bool
    {
        if (null !== self::$newImplement) {
            return self::$newImplement;
        }

        $version = ltrim((string) InstalledVersions::getPrettyVersion('topthink/framework'), 'v');
        if (preg_match('~^(\d+\.?)+$~', $version)) {
            $newImplement = version_compare($version, '8.1.2', '>');
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

        if ($msg instanceof Stringable) {
            $msg = (string) $msg;
        }

        if (\is_string($msg) && !empty($context)) {
            $replace = [];
            foreach ($context as $key => $val) {
                $k = '{'.$key.'}';
                if (!str_contains($msg, $k)) {
                    continue;
                }
                if (!\is_string($val)) {
                    if ($val instanceof Stringable) {
                        $val = (string) $val;
                    } elseif (\is_object($val)) {
                        $val = $this->dumpVar($val);
                    } else {
                        $val = var_export($val, true);
                    }
                }
                $replace[$k] = $val;
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

    private function dumpVar(mixed $value): string
    {
        if (!isset($this->cloner)) {
            $this->cloner = new VarCloner();
            $this->cloner->setMinDepth(3);
            $this->cloner->setMaxString(128);
            $this->cloner->setMaxItems(100);
        }
        if (!isset($this->dumper)) {
            $this->dumper = new CliDumper(
                flags: AbstractDumper::DUMP_LIGHT_ARRAY |
                AbstractDumper::DUMP_COMMA_SEPARATOR |
                AbstractDumper::DUMP_TRAILING_COMMA |
                AbstractDumper::DUMP_STRING_LENGTH
            );
            $this->dumper->setColors(false);
            $this->dumper->setStyles([]);
            $this->dumper->setDisplayOptions([
                'fileLinkFormat' => null,
            ]);
        }

        return $this->dumper->dump($this->cloner->cloneVar($value), true);
    }
}
