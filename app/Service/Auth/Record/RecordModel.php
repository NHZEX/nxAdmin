<?php

namespace app\Service\Auth\Record;

use think\Model;

/**
 * model: 活动日志.
 *
 * @property int    $id
 * @property int    $user_id      用户ID
 * @property int    $create_time  创建时间
 * @property string $auth_name
 * @property string $target
 * @property string $method
 * @property string $url
 * @property string $ip
 * @property int    $http_code
 * @property string $resp_code
 * @property string $resp_message
 * @property array  $details
 *
 * ↓↓ virtual props ↓↓
 * @property int    $group_id
 * @property string $module
 */
class RecordModel extends Model
{
    protected $table = 'activity_log';
    protected $pk = 'id';
    protected $convertNameToCamel = false;
    protected $type = [
        'details' => 'json',
    ];
}
