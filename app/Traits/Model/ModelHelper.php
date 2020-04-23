<?php
declare(strict_types=1);

namespace app\Traits\Model;

use app\Model\Base;
use Closure;
use think\db\Query;
use think\Model;
use function call_user_func;
use function count;
use function is_callable;
use function is_numeric;
use function is_string;

/**
 * Trait ModelHelper
 * @package app\Traits\Model
 * @mixin Model
 * @mixin Base
 */
trait ModelHelper
{
    /**
     * @return string
     */
    public static function getTableName(): string
    {
        return (new static())->getTable();
    }

    /**
     * 构建子查询
     * @param Closure $closure
     * @param string|null $field
     * @return Closure
     */
    public static function subQuery(Closure $closure, ?string $field)
    {
        return function (Query $query) use ($closure, $field) {
            $query->table(static::getTableName());
            $closure($query);

            if (!empty($field)) {
                $query->field($field);
            }
        };
    }

    /**
     * 生成选项列表
     * @param array|null    $argv
     * @param callable|null $where
     * @return array
     */
    public static function buildOption(array $argv = null, callable $where = null): array
    {
        if ($argv === null) {
            $argv = static::BUILD_OPTION_ARGV;
        }
        if (count($argv) < 2) {
            return [];
        }

        $model = [];
        foreach ($argv as $k => $v) {
            if (is_numeric($k)) {
                if ($k === 0) {
                    $model['value'] = $v;
                } elseif ($k === 1) {
                    $model['label'] = $v;
                } else {
                    if (!is_string($v)) {
                        continue;
                    }
                    $model[$v] = $v;
                }
            } else {
                $model[$k] = $v;
            }
        }

        $self = new static();
        if ($where) {
            $self = $self->where($where);
        }
        $result = [];
        foreach ($self->cursor() as $item) {
            $tmp = [];
            foreach ($model as $k => $v) {
                if (is_callable($v)) {
                    $tmp[$k] = call_user_func($v, $item);
                } else {
                    $tmp[$k] = $item->getAttr($v);
                }
            }
            $result[] = $tmp;
        }
        return $result;
    }
}
