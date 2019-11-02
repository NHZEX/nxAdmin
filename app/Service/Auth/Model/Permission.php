<?php
declare(strict_types=1);

namespace app\Service\Auth\Model;

use think\Model;

/**
 * Class Permission
 * @package app\Service\Auth\Model
 *
 * @property int    $id      节点id
 * @property int    $pid     父节点id
 * @property string $name    权限名称
 * @property string $control 授权内容
 */
class Permission extends Model
{
    protected $table = 'permission';


}
