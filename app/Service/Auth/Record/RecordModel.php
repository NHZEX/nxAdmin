<?php

namespace app\Service\Auth\Record;

use think\Model;

/**
 * model: 活动日志
 * @property int    $id
 * @property int    $user_id
 * @property int    $group_id
 * @property int    $create_time
 * @property string $auth_name
 * @property string $module
 * @property string $target
 * @property string $method
 * @property string $url
 * @property string $ip
 * @property int    $http_code
 * @property string $resp_code
 * @property string $resp_message
 * @property array  $details
 */

class RecordModel extends Model
{
    protected $table = 'activity_log';
    protected $pk    = 'id';
    protected $type = [
        'details' => 'json',
    ];
}
