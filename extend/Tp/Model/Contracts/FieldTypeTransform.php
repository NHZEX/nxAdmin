<?php

declare(strict_types=1);

namespace Tp\Model\Contracts;

use think\Model;

interface FieldTypeTransform
{
    /**
     * @param mixed  $value
     * @param Model  $model
     * @return mixed
     */
    public static function modelReadValue($value, $model);

    /**
     * @param mixed  $value
     * @param Model  $model
     * @return mixed
     */
    public static function modelWriteValue($value, $model);
}
