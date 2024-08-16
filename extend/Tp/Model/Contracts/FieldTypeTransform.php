<?php

declare(strict_types=1);

namespace Tp\Model\Contracts;

use think\Model;

/**
 * @deprecated 改为使用Orm最新内置类
 */
interface FieldTypeTransform
{
    /**
     * @param Model $model
     */
    public static function modelReadValue($value, $model);

    /**
     * @param Model $model
     */
    public static function modelWriteValue($value, $model);
}
