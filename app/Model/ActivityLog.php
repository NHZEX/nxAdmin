<?php
/**
 * Created by Automatic build
 * User: System
 * Date: 2021/05/14
 * Time: 11:48
 */

namespace app\Model;

/**
 * @property string $auth_name
 * @property int    $create_time
 * @property array  $details
 * @property int    $id
 * @property string $ip
 * @property string $message
 * @property string $method
 * @property string $resp_status
 * @property string $url
 * @property int    $user_id
 */
class ActivityLog extends Base
{
    protected $table = 'activity_log';
    protected $pk = 'id';
}
