<?php
/**
 * Created by Automatic build
 * User: System
 * Date: 2019/07/04
 * Time: 11:40
 */

namespace app\Model;

/**
 * Model: 权限规则
 * Class PermissionRule
 * @package app\Model
 *
 * @property int    $id
 * @property int    $pid          父节点
 * @property int    $genre        规则类型
 * @property int    $status       规则状态
 * @property int    $create_time  创建时间
 * @property int    $update_time  更新时间
 * @property int    $delete_time  删除时间
 * @property string $objn         规则名称
 * @property string $description  规则描述
 * @property string $condition    规则条件
 * @property int    $lock_version
 */
class PermissionRule extends Base
{
    protected $table = 'permission_rule';
    protected $pk = 'id';
    
}
