<?php

declare(strict_types=1);

namespace app\Traits;

use app\Helper\WhereHelper;
use Closure;
use think\Request;

/**
 * Trait ControllerHelper.
 *
 * @property Request $request
 */
trait ControllerHelper
{
    /**
     * 数据表字段名 - 页面属性，映射.
     */
    public function buildParam(array $mapping): array
    {
        $data = [];
        $input = $this->request->param();
        foreach ($mapping as $name => $alias) {
            if (\is_int($name)) {
                $name = $alias;
            }
            if (isset($input[$alias])) {
                $data[$name] = $input[$alias];
            }
        }

        return $data;
    }

    /**
     * 构建筛选条件.
     *
     * @param array $input 输入数据
     * @param array<array{0:string, 1: string, 2?: string, empty?: callable|(callable(string, array): bool), find?: array<string>|callable (array, input): string}> $where
     *                                                                                                                                                                     筛选设置 ['字段名', '操作符', '值', 'empty' => '值验证回调', 'find' => '值来源字段名']
     */
    public function buildWhere(array $input, array $where): array
    {
        return WhereHelper::buildWhere($input, $where);
    }

    /**
     * 构建筛选条件 (延迟闭包).
     *
     * @see buildWhere
     */
    public function buildWhereClosure(array $input, array $where): Closure
    {
        return WhereHelper::buildWhereClosure($input, $where);
    }

    /**
     * @return array{string, string}|null [$field => $order]
     */
    public function buildOrder(?array $input = null, string $orderField = '_sort', ?string $tableName = null): ?array
    {
        return WhereHelper::buildOrder($input, $orderField, $tableName);
    }
}
