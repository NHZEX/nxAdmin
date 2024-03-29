<?php

namespace Tp\Model\Traits;

use think\db\Raw;
use think\Model;
use function addslashes;
use function array_is_list;
use function array_map;
use function is_array;
use function is_bool;
use function is_numeric;
use function join;
use function strlen;
use function substr;

/**
 * Trait OptimLock
 * @package Tp\Model\Traits
 * @mixin Model
 */
trait MysqlJson
{
    /**
     * 生成Json字段查询代码
     * @param string      $field
     * @param string      $path
     * @param string|null $alias
     * @return string
     */
    public static function queryJsonField(string $field, string $path, ?string $alias = null): string
    {
        return "`{$field}`->>'$.{$path}'" . ($alias ? " AS {$alias}" : " AS {$path}");
    }

    /**
     * 设置JsonData
     * @param string           $field
     * @param string           $path
     * @param string|int|array $value
     * @return $this
     */
    public function setJsonData(string $field, string $path, $value)
    {
        // 处理输入值
        $value = self::jsonValue($value);

        // 写入json数据
        $raw          = new Raw(
            "JSON_SET(IF(JSON_TYPE(`{$field}`)='NULL',JSON_OBJECT(),`{$field}`), '$.{$path}', {$value})"
        );
        $this->$field = $raw;
        return $this;
    }

    /**
     * 设置JsonData
     * @param string $field
     * @param array  $vs
     * @return $this
     */
    public function setJsonDatas(string $field, array $vs)
    {
        if (empty($vs)) {
            return $this;
        }

        $sets = '';
        foreach ($vs as $v) {
            [$path, $value] = $v;
            $value = self::jsonValue($value);
            $sets  .= ", '$.{$path}', {$value}";
        }

        // 写入json数据
        $raw          = new Raw("JSON_SET(IF(JSON_TYPE(`{$field}`)='NULL',JSON_OBJECT(),`{$field}`) {$sets})");
        $this->$field = $raw;
        return $this;
    }

    /**
     * Mysql Json 代码生成
     * @param mixed $value
     * @return string
     */
    protected static function jsonValue($value): string
    {
        if (is_numeric($value)) {
            return (string) $value;
        } elseif (is_bool($value)) {
            return (string) $value;
        } elseif (is_array($value)) {
            if (array_is_list($value)) {
                $tmp = '';
                foreach ($value as $key => $v) {
                    $tmp .= "'{$key}', " . self::jsonValue($v) . ',';
                }
                $tmp   = substr($tmp, 0, strlen($tmp) - 1);
                $value = "JSON_OBJECT({$tmp})";
                unset($tmp);
            } else {
                $value = join(',', array_map(fn ($v) => self::jsonValue($v), $value));
                $value = "JSON_ARRAY({$value})";
            }
        } else {
            $value = addslashes((string) $value);
            $value = "'{$value}'";
        }

        return $value;
    }
}
