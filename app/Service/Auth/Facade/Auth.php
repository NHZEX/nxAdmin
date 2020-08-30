<?php
declare(strict_types=1);

namespace app\Service\Auth\Facade;

use app\Model\AdminUser;
use think\Facade;
use Zxin\Think\Auth\AuthGuard;

/**
 * Class Auth
 * @package app\Service\Auth\Facade
 * @method AuthGuard instance() static
 * @method int|string id() static
 * @method int userGenre() static // todo 使用别的方式实现
 * @method int userRoleId() static
 * @method AdminUser user() static
 * @method bool check() static
 * @method bool can(string $name) static
 */
class Auth extends Facade
{
    protected static function getFacadeClass()
    {
        return 'auth';
    }
}
