<?php

declare(strict_types=1);

namespace app\Traits\Model;

use app\Model\Base;
use Closure;
use Generator;
use think\db\Query;
use think\db\Raw;
use think\Model;
use function array_diff;
use function array_map;
use function call_user_func;
use function count;
use function is_array;
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
        /* @phpstan-ignore-next-line 必须是 static */
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
            $query->table(self::getTableName());
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
     * @param callable|null $dbCallback
     * @return array
     */
    public static function buildOption(array $argv = null, callable $where = null, callable $dbCallback = null): array
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

        $self = new static(); /** @phpstan-ignore-line 必须是 static */
        if ($where) {
            $self = $self->where($where);
        }
        if ($dbCallback) {
            $result = $dbCallback($self);
            if (!empty($result)) {
                $self = $result;
            }
            unset($result);
        }
        $result = [];
        foreach ($self->cursor() as $item) {
            $tmp = [];
            foreach ($model as $k => $v) {
                if ($v instanceof Closure || str_starts_with($v, '\\') || (is_array($v) && is_callable($v))) {
                    $tmp[$k] = call_user_func($v, $item);
                } elseif (is_string($v)) {
                    $tmp[$k] = $item->getAttr($v);
                } else {
                    $tmp[$k] = null;
                }
            }
            $result[] = $tmp;
        }
        return $result;
    }

    public static function getVisibleFields(array $exclude = [], ?string $as = null, array $only = []): array
    {
        $model = new static();
        $fields = $model->getTableFields();
        $fields = array_diff($fields, $model->disuse, $exclude);
        if ($only) {
            $fields = array_intersect($only, $fields);
        }
        if ($as) {
            return array_map(fn ($field) => "`{$as}`.`{$field}`", $fields);
        } else {
            return $fields;
        }
    }

    public static function chunkIter(Query|Model $modelQuery, int $limit, string $column, string $alias = null, string $order = 'asc', ?int $startPosition = null): Generator
    {
        $column  = $column ?: $modelQuery->getPk();

        if (strpos($column, '.')) {
            [, $key] = explode('.', $column);
        } else {
            $key = $column;
        }
        if (empty($alias)) {
            $alias = $key;
        }

        $bind = $modelQuery->getBind(false);

        $lastId = $startPosition;

        do {
            $query = (clone $modelQuery)
                ->removeOption('order')
                ->limit($limit);

            if (null !== $lastId) {
                $query->where($column, 'asc' == strtolower($order) ? '>' : '<', $lastId);
            }

            $resultSet = $query
                ->order($column, $order)
                ->bind($bind)
                ->select();

            if ($resultSet->isEmpty()) {
                break;
            }

            yield $resultSet;

            if ($limit > $resultSet->count()) {
                break;
            }

            $end    = $resultSet->pop();
            $lastId = is_array($end) ? $end[$alias] : $end->getData($alias);
        } while (true);
    }

    public static function chunkIterEach(Query|Model $modelQuery, int $limit, string $column, string $alias = null, string $order = 'asc', ?int $startPosition = null, ?callable $preCb = null): Generator
    {
        foreach (self::chunkIter($modelQuery, $limit, $column, $alias, $order, $startPosition) as $i => $items) {
            $itemData = $items->getIterator();
            if ($preCb) {
                $result = call_user_func($preCb, $itemData, $i);
                if (is_iterable($result)) {
                    $itemData = $result;
                }
            }
            foreach ($itemData as $ii => $item) {
                yield $i * $limit + $ii => $item;
            }
        }
    }

    public static function jsonObjectValueSet(string $field, array $set = []): ?Raw
    {
        if (empty($set)) {
            return null;
        }

        $data = [];
        $values = [];

        foreach ($set as $key => $val) {
            $data[] = "'{$key}'";
            if (\is_object($val) || is_array($val)) {
                $data[] = 'CONVERT(?, JSON)';
                $values[] = \json_encode_ex($val);
            } else {
                $data[] = '?';
                $values[] = $val;
            }
        }

        $str = \implode(', ', $data);
        return new Raw(
            "json_set(`{$field}`, {$str})",
            $values,
        );
    }
}
