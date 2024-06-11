<?php

namespace app\Service\Auth;

use app\Model\AdminUser;
use Zxin\Think\Auth\AuthManager;

/**
 * Class AuthManager.
 *
 * @method static AdminUser user()
 */
class AuthHelper extends AuthManager
{
    public static function userGenre(): int
    {
        return self::instance()->__get(__FUNCTION__);
    }

    public static function userRoleId(): int
    {
        return self::instance()->__get(__FUNCTION__);
    }

    public static function isSuperAdmin(): bool
    {
        return AdminUser::GENRE_SUPER_ADMIN === self::userGenre();
    }

    public static function isAdminUser(): bool
    {
        return AdminUser::GENRE_ADMIN === self::userGenre();
    }

    public static function isAgentUser(): bool
    {
        return AdminUser::GENRE_AGENT === self::userGenre();
    }

    public static function anyAdmin(): bool
    {
        $genre = self::userGenre();

        return AdminUser::GENRE_ADMIN === $genre || AdminUser::GENRE_SUPER_ADMIN === $genre;
    }
}
