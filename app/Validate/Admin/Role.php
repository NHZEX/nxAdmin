<?php
declare(strict_types=1);

namespace app\Validate\Admin;

use app\Validate\Base;

class Role extends Base
{
    // todo genre、status 从模型获取有效范围
    protected $rule = [
        'genre'  => 'require|number',
        'status' => 'require|number',
        'name'   => 'require|length:3,64',
        'ext'    => 'require|array',
        'lock_version' => 'require|number',
    ];
}
