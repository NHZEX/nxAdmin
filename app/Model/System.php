<?php
declare(strict_types=1);

namespace app\Model;

/**
 * Class System
 * @package app\Model
 * @property int $id
 * @property string $laber
 * @property string $value
 */
class System extends Base
{
    protected $name = 'system';
    protected $pk = 'label';

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
        $db = app()->db->connect();
        $database = $db->getConfig('database');
        /** @noinspection SqlNoDataSourceInspection */
        /** @noinspection SqlDialectInspection $sql */
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
}
