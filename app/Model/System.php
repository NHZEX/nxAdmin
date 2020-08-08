<?php
declare(strict_types=1);

namespace app\Model;

use app\Service\Transaction\MainTrans;
use function array_map;
use function bin2hex;
use function openssl_random_pseudo_bytes;
use function str_starts_with;
use function substr;
use function time;

/**
 * Class System
 * @package app\Model
 * @property int    $id
 * @property string $laber
 * @property string $value
 */
class System extends Base
{
    protected $name = 'system';
    protected $pk   = 'label';

    protected $schema = [
        'label' => 'string',
        'value' => 'string',
    ];

    /**
     * 是否可用
     * @return bool
     */
    public static function isAvailable()
    {
        $db       = app()->db->connect();
        $database = $db->getConfig('database');
        /** @noinspection SqlNoDataSourceInspection */
        $sql = "select * from `INFORMATION_SCHEMA`.`TABLES` where TABLE_SCHEMA='{$database}' and TABLE_NAME='system'";
        return count($db->query($sql)) > 0;
    }

    /**
     * 查询一个值
     * @param string      $label
     * @param string|null $default
     * @return mixed
     */
    public static function getLabel(string $label, string $default = null): ?string
    {
        return self::where('label', '=', $label)->value('value', $default);
    }

    /**
     * 设置一个值
     * @param string $label
     * @param string $value
     * @return bool
     */
    public static function setLabel(string $label, string $value): bool
    {
        self::where('label', '=', $label)
            ->replace(true)
            ->data(['label' => $label, 'value' => $value])
            ->insert();

        return true;
    }

    /**
     * @param string $label
     * @param int    $ttl
     * @return false|string
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
            $value = self::where('label', $label)
                ->lock(true)
                ->value('value', null);
            if ($value === null) {
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

    /**
     * @param string $label
     * @param string $key
     * @param bool   $force
     * @return bool
     */
    public static function unLock(string $label, string $key, bool $force = false): bool
    {
        return MainTrans::callback(function () use ($label, $key, $force) {
            $value = self::where('label', $label)
                ->lock(true)
                ->value('value', null);
            if ($force || ($value === null || str_starts_with($value, "_lock:{$key}"))) {
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
        if ($value === null) {
            return null;
        }

        return self::parseLock($value);
    }

    protected static function parseLock(string $info)
    {
        $isLock = str_starts_with($info, "_lock:");

        return [
            'lock' => $isLock,
            'token' => $isLock ? substr($info, 5, 8) : null,
            'time' => (int) ($isLock ? substr($info, 15) : $info),
        ];
    }
}
