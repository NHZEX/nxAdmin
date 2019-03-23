<?php
/**
 * Created by PhpStorm.
 * User: Johnson
 * Date: 2019/1/21
 * Time: 18:04
 */

namespace app\validate;

class Role extends Base
{
    protected $rule = [
        'id' => 'require|isPositiveInteger',
        'hashArr' => 'require|array',
    ];

    protected $scene = [
        'toPermission' => ['id'],
        'permission' => ['id', 'hashArr'],
    ];
}
