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

    public static function userGenre(): int
    {
        return self::instance()->__get(__FUNCTION__);
    }

    public static function userRoleId(): int
    {
        return self::instance()->__get(__FUNCTION__);
    }
}
