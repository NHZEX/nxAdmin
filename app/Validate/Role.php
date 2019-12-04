<?php
/**
 * Created by PhpStorm.
 * Date: 2019/1/21
 * Time: 18:04
 */

namespace app\Validate;

class Role extends Base
{
    protected $rule = [
        'id' => 'require|isPositiveInteger',
        'permission' => 'require|array',
    ];

    protected $scene = [
        'toPermission' => ['id'],
        'permission' => ['id', 'permission'],
    ];
}
