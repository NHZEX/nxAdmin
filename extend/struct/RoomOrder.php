<?php
/**
 * Created by PhpStorm.
 * User: Johnson
 * Date: 2019/2/26
 * Time: 17:58
 */

namespace struct;


/**
 * 房间
 * Class HotelRoom
 * @package app\model
 * @property string $serial 房单号
 * @property string $bill_serial 账单号
 * @property int $room_id 房间id
 * @property int $start_time 开始周期
 * @property int $end_time 结束周期
 * @property int $genre 操作类型
 * @property int $mode 入住方式
 * @property int $status 状态
 * @property array $snap 快照
 */
class RoomOrder extends Base
{
    public $serial = null;

    public $bill_serial = null;

    public $room_id = null;

    public $start_time = null;

    public $end_time = null;

    public $genre = null;

    public $mode = null;

    public $status = null;

    public $snap = null;

}