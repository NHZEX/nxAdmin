<?php
/**
 * Created by PhpStorm.
 * User: Johnson
 * Date: 2019/2/23
 * Time: 14:51
 */

namespace struct;


/**
 * 酒店全局设置
 * Class HotelConfigStruct
 * @package struct
 *
 * @property int $hour_cycle_time 钟点房周期时间
 * @property int $day_cycle_time  全日房周期时间
 * @property int $earliest_time   最早入住时间
 * @property int $stay_cost_type  钟点房续全日房计费方式
 */
class HotelConfigStruct extends Base
{
    const HOUR_CYCLE_TIME = 'hour_cycle_time';
    const DAY_CYCLE_TIME = 'day_cycle_time';
    const EARLIEST_TIME = 'earliest_time';
    const TIME_DICT = [
        self::HOUR_CYCLE_TIME => '钟点房周期时间',
        self::DAY_CYCLE_TIME => '全日房周期时间',
        self::EARLIEST_TIME => '最早入住时间',
    ];

    //钟点房续全日房计费方式
    const STAY_TYPE_DICT = [
        1 => '以交费用 + 全日房费用（费用叠加）',
        2 => '多出费用不退款, 少则补全（不退少补）',
    ];

    public $hour_cycle_time = null;

    public $day_cycle_time = null;

    public $earliest_time = null;

    public $stay_type = null;
}