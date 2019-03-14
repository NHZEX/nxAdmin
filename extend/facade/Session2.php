<?php
/**
 * Created by PhpStorm.
 * User: Auoor
 * Date: 2019/3/10
 * Time: 17:40
 */

namespace facade;

use think\facade\Session;

/**
 * Class Session2
 * @package facade
 * @method string getId(bool $regenerate = true) static sessionId获取
 * @method string getName() static sessionName获取
 */
class Session2 extends Session
{
}
