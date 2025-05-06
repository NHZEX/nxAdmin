<?php

namespace app\Helper;

use app\Helper\WhereHelper\ConditionBuilder;
use Closure;
use Composer\Pcre\Preg;
use think\db\Query;
use function array_filter;
use function array_merge;
use function explode;

class WhereHelper
{
    /**
     * 构建筛选条件.
     *
     * @deprecated
     *
     * @param array $input 输入数据
     * @param array<array{0:string, 1: string, 2?: string, empty?: callable|(callable(string, array): bool), find?: array<string>|callable (array, string): string, tf?: callable}> $where 筛选设置 ['字段名', '操作符', '值', 'empty' => '值验证回调', 'find' => '值来源字段名']
     */
    public static function buildWhere(array $input, array $where): array
    {
        $data = [];
        foreach ($where as $item) {
            if (\count($item) >= 2) {
                [$whereField, $op] = $item;
                $inputField = $whereField;
                $inputFields = [];

                if (isset($item['find'])) {
                    $find = $item['find'];
                    if (\is_callable($find)) {
                        $inputFields[] = $find($input, $inputField);
                    } elseif (\is_array($find)) {
                        $inputFields = array_merge($inputFields, $find);
                    }
                } else {
                    $inputFields[] = $inputField;
                }

                foreach ($inputFields as $field) {
                    if (!isset($input[$field])) {
                        continue;
                    }
                    $condition = $input[$field];
                    // transform
                    if (isset($item['tf']) && \is_callable($item['tf'])) {
                        $condition = \call_user_func($item['tf'], $condition);
                    }
                    if (isset($item['empty'])) {
                        if (\is_callable($item['empty']) && !$item['empty']($condition, $input)) {
                            continue;
                        }
                    } else {
                        if (empty($condition)) {
                            continue;
                        }
                    }
                    $parse = $item[2] ?? null;
                    $data[] = [
                        $whereField,
                        $op,
                        (isset($parse) && \is_callable($parse)) ? $parse($condition, $field) : $condition,
                    ];
                    break;
                }
            }
        }

        return $data;
    }

    /**
     * 构建筛选条件 (延迟闭包).
     *
     * @see buildWhere
     */
    public static function buildWhereClosure(array $input, array $where): Closure
    {
        return function (Query $query) use ($input, $where): void {
            $tableName = $query->getTable();
            $tableName = \is_array($tableName) ? $tableName[array_key_first($tableName)] : $tableName;

            $where = static::buildWhere($input, $where);
            $output = [];
            foreach ($where as $value) {
                $value[0] = "{$tableName}.{$value[0]}";
                $output[] = $value;
            }
            $query->where($output);
        };
    }

    /**
     * @return array{string, string}|null [$field => $order]
     */
    public static function buildOrder(array $input, string $orderField = '_sort', ?string $tableName = null): ?array
    {
        $sort = $input[$orderField] ?? null;
        if (empty($sort) || !str_contains((string) $sort, ':')) {
            return null;
        }
        $sort = array_filter(explode(':', (string) $sort, 2));
        if (2 !== \count($sort)) {
            return null;
        }
        if ('asc' !== $sort[1] && 'desc' !== $sort[1]) {
            return null;
        }
        $fieldName = $tableName ? "{$tableName}.{$sort[0]}" : $sort[0];

        return [
            $fieldName => $sort[1],
        ];
    }

    /**
     * @param array<ConditionBuilder> $conditionSet
     */
    public static function whereBuilder(array $input, array $conditionSet): array
    {
        $items = [];
        foreach ($conditionSet as $condition) {
            $result = $condition($input);
            if (null === $result) {
                continue;
            }
            $items[] = $result;
        }

        return $items;
    }

    /**
     * @param array<ConditionBuilder> $conditionSet
     */
    public static function whereBuilderMatchOne(array $input, array $conditionSet): ?array
    {
        foreach ($conditionSet as $condition) {
            $result = $condition($input);
            if (null === $result) {
                continue;
            }

            return $result;
        }

        return null;
    }

    public static function conditionBuilder(
        string|Closure $field,
        ?string $operator = null,
        string|array|Closure|null $inputKey = null,
    ): ConditionBuilder {
        return ConditionBuilder::make($field, $inputKey, $operator);
    }

    public static function conditionBuilderByDate(string $field, string|array|Closure|null $inputKey = null, bool $dateStr = false, bool $msec = false, bool $timeRange = false, ?string $default = null): ConditionBuilder
    {
        return ConditionBuilder::make($field, $inputKey, 'between', default: $default)
            ->test(before: fn ($val) => $val && (\is_string($val) || \is_array($val)))
            ->convert(function ($val) use ($dateStr, $msec, $timeRange) {
                if (\is_string($val)) {
                    return self::parseDate($val, $dateStr, $msec);
                } elseif (2 === \count($val)) {
                    $d1 = (int) $val[0];
                    $d2 = (int) $val[1];
                    $dateFormat = $timeRange ? ['Y-m-d H:i:00', 'Y-m-d H:i:59'] : ['Y-m-d 00:00:00', 'Y-m-d 23:59:59'];
                    if ($d1 > 0 && $d2 >= $d1) {
                        $t1 = $dateStr ? date($dateFormat[0], $d1) : strtotime(date($dateFormat[0], $d1));
                        $t2 = $dateStr ? date($dateFormat[1], $d2) : strtotime(date($dateFormat[1], $d2));
                        if ($msec) {
                            $t1 = "{$t1}000";
                            $t2 = "{$t2}000";
                        }

                        return [$t1, $t2];
                    }
                }

                return null;
            });
    }

    private static function parseDate(string $command, bool $dateStr, bool $msec = false): ?array
    {
        if ('all' === $command) {
            return null;
        }
        $result = match ($command) {
            'today' => [date('Y-m-d 00:00:00'), date('Y-m-d 23:59:59')],
            'yesterday' => [date('Y-m-d 00:00:00', strtotime('-1 day')), date('Y-m-d 23:59:59', strtotime('-1 day'))],
            'this_week' => [date('Y-m-d 00:00:00', strtotime('this week')), date('Y-m-d 23:59:59')],
            'this_month' => [date('Y-m-01 00:00:00'), date('Y-m-d 23:59:59')],
            'this_year' => [date('Y-01-01 00:00:00'), date('Y-m-d 23:59:59')],
            default => true,
        };
        if (true !== $result) {
            $t1 = $dateStr ? $result[0] : strtotime($result[0]);
            $t2 = $dateStr ? $result[1] : strtotime($result[1]);

            return [$msec ? "{$t1}000" : $t1, $msec ? "{$t2}000" : $t2];
        }

        if (Preg::isMatch('/^last_(\d+)d/', $command)) {
            $days = substr($command, 5, -1);
            $dateRange = [date('Y-m-d 00:00:00', strtotime('-'.$days.' day')), date('Y-m-d 23:59:59')];
            $t1 = $dateStr ? $dateRange[0] : strtotime($dateRange[0]);
            $t2 = $dateStr ? $dateRange[1] : strtotime($dateRange[1]);

            return [$msec ? "{$t1}000" : $t1, $msec ? "{$t2}000" : $t2];
        } else {
            return null;
        }
    }

    public static function conditionBuilderByNumberRange(string $field, string|array|Closure $inputKey, ?Closure $format = null): ConditionBuilder
    {
        return ConditionBuilder::make(function (string $value) use ($field, $format) {
            if (!Preg::match('/^(\d+(?:\.\d+)?|nil)~(\d+(?:\.\d+)?|nil)$/', $value, $matches)) {
                return null;
            }
            if ('nil' === $matches[1] && 'nil' === $matches[2]) {
                return null;
            }
            $min = $matches[1];
            $max = $matches[2];

            if ($format) {
                if ('nil' !== $min) {
                    $min = \call_user_func($format, $min);
                }
                if ('nil' !== $max) {
                    $max = \call_user_func($format, $max);
                }
            }

            if ('nil' !== $max) {
                if (str_contains($max, '.')) {
                    [$num1, $num2] = explode('.', $max);
                    $max = $num1.'.'.str_pad(str_pad($num2, 3, '0'), 8, '9');
                } else {
                    $max .= '.00099999';
                }
            }

            if ('nil' === $max) {
                return [$field, '>=', $min];
            } elseif ('nil' === $min) {
                return [$field, '<=', $max];
            }

            return [$field, 'between', [$min, $max]];
        }, $inputKey, 'between')
            ->test(before: fn ($val) => $val && \is_string($val));
    }
}
