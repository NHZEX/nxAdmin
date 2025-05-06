<?php

declare(strict_types=1);

namespace app\Model;

use app\Service\Context;
use app\Service\Transaction\MainTrans;
use function array_map;
use function bin2hex;
use function is_countable;
use function openssl_random_pseudo_bytes;
use function str_starts_with;
use function substr;
use function time;

/**
 * model: 系统表.
 *
 * @property string $label 标签
 * @property string $value 值
 * @property int $created_at
 * @property int $updated_at
 * @property int $lock_version
 */
class System extends Base
{
    protected $name = 'system';
    protected $pk = 'label';
    protected $convertNameToCamel = false;

    protected $schema = [
        'label' => 'string',
        'value' => 'string',
        'created_at' => 'int',
        'updated_at' => 'int',
        'lock_version' => 'int',
    ];

    /**
     * 是否可用.
     *
     * @return bool
     */
    public static function isAvailable()
    {
        $db = app()->db->connect();
        $database = $db->getConfig('database');
        /** @noinspection SqlNoDataSourceInspection SqlDialectInspection */
        $sql = "select * from `INFORMATION_SCHEMA`.`TABLES` where TABLE_SCHEMA='{$database}' and TABLE_NAME='system'";

        return (is_countable($db->query($sql)) ? \count($db->query($sql)) : 0) > 0;
    }

    /**
     * 查询一个值
     */
    public static function getLabel(string $label, ?string $default = null, ?int &$lockVersion = 0, ?int &$updatedAt = 0): ?string
    {
        $result = Context::rememberData("system-label:{$label}", function () use ($label) {
            return \app()->cache->remember("system-label:{$label}", function () use ($label) {
                /** @var System|null $item */
                $item = (new System())
                    ->where('label', '=', $label)
                    ->find();

                if ($item) {
                    return [
                        'value' => $item->value,
                        'updated_at' => $item->updated_at,
                        'lock_version' => $item->lock_version,
                    ];
                } else {
                    return null;
                }
            }, 1800);
        });

        if ($result) {
            $lockVersion = $result['lock_version'];
            $updatedAt = $result['updated_at'];
            return $result['value'] ?? $default;
        }

        return $default;
    }

    /**
     * 设置一个值
     * @deprecated
     */
    public static function setLabel(string $label, string $value): bool
    {
        self::where('label', '=', $label)
            ->replace(true)
            ->data(['label' => $label, 'value' => $value])
            ->insert();

        $cacheKey = "system-label:{$label}";
        \app()->cache->delete($cacheKey);
        Context::removeData($cacheKey);

        return true;
    }

    public static function setLabelEx(string $label, string $value, ?int $lockVersion = null): void
    {
        MainTrans::callback(function () use ($label, $value, $lockVersion) {
            /** @var System|null $item */
            $item = (new System())->where('label', '=', $label)->find();
            $nowTime = time();
            if ($item && $value !== $item->value) {
                if ($lockVersion !== null && $item->lock_version !== $lockVersion) {
                    throw new \LogicException("set label {$label} lock version timeout");
                } else {
                    $item->value = $value;
                    $item->updated_at = $nowTime;
                    $item->save();
                }
            } elseif (empty($item)) {
                (new System())->insert([
                    'label' => $label,
                    'value' => $value,
                    'created_at' => $nowTime,
                    'updated_at' => $nowTime,
                    'lock_version' => 0,
                ]);
            }
        });
        $cacheKey = "system-label:{$label}";
        \app()->cache->delete($cacheKey);
        Context::removeData($cacheKey);
    }

    /**
     * @return false|string
     * @deprecated
     */
    public static function setLock(string $label, int $ttl)
    {
        $generateKey = function (&$token, $ttl) {
            $token = bin2hex(openssl_random_pseudo_bytes(4));
            if ($ttl > 0) {
                $ttl += time();
            } else {
                $ttl = 0;
            }

            return "_lock:{$token}:{$ttl}";
        };

        return MainTrans::callback(function () use ($label, $ttl, $generateKey) {
            $token = null;
            $value = self::where('label', $label)
                ->lock(true)
                ->value('value', null);
            if (null === $value) {
                self::insert([
                    'label' => $label,
                    'value' => $generateKey($token, $ttl),
                ]);

                return $token;
            }
            if (str_starts_with($value, '_lock:') && (int) substr($value, 15) > time()) {
                return false;
            }
            self::where('label', $label)
                ->update([
                    'value' => $generateKey($token, $ttl),
                ]);

            return $token;
        });
    }

    public static function unLock(string $label, string $key, bool $force = false): bool
    {
        return MainTrans::callback(function () use ($label, $key, $force) {
            $value = self::where('label', $label)
                ->lock(true)
                ->value('value', null);
            if ($force || (null === $value || str_starts_with($value, "_lock:{$key}"))) {
                self::where('label', $label)
                    ->update([
                        'value' => time(),
                    ]);

                return true;
            }

            return false;
        });
    }

    public static function lockStats(array $labels)
    {
        $list = self::whereIn('label', $labels)
            ->column('value', 'label');
        if (empty($list)) {
            return null;
        }

        return array_map([self::class, 'parseLock'], $list);
    }

    public static function lockStat(string $label)
    {
        $value = self::where('label', $label)
            ->value('value', null);
        if (null === $value) {
            return null;
        }

        return self::parseLock($value);
    }

    protected static function parseLock(string $info)
    {
        $isLock = str_starts_with($info, '_lock:');

        return [
            'lock' => $isLock,
            'token' => $isLock ? substr($info, 5, 8) : null,
            'time' => (int) ($isLock ? substr($info, 15) : $info),
        ];
    }
}
