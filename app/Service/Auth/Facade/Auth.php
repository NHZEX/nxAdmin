<?php
declare(strict_types=1);

namespace app\Service\Auth\Facade;

use app\Model\AdminUser;
use app\Service\Auth\AuthGuard;
use think\Facade;

/**
 * Class Auth
 * @package app\Service\Auth\Facade
 * @method AuthGuard instance()
 * @method int|string id()
 * @method AdminUser user()
 * @method bool check()
 * @method bool can(string $name)
 */
class Auth extends Facade
{
    protected static function getFacadeClass()
    {
        return 'auth';
    }
}
