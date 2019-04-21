<?php
/**
 * Created by PhpStorm.
 * User: Auoor
 * Date: 2019/3/10
 * Time: 17:40
 */

namespace Tp\Facade;

use think\facade\Session as ThinkSession;

/**
 * Class Session
 * @package Tp\Facade
 * @method string getId(bool $regenerate = true) static sessionId获取
 */
class Session extends ThinkSession
{
}
