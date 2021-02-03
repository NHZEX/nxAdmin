<?php

namespace app\Service\Auth;

use app\Model\AdminUser;
use Zxin\Think\Auth\AuthManager;

/**
 * Class AuthManager
 * @package app\Service\Auth
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
        return self::userGenre() === AdminUser::GENRE_SUPER_ADMIN;
    }

    public static function isAdminUser(): bool
    {
        return self::userGenre() === AdminUser::GENRE_ADMIN;
    }

    public static function isAgentUser(): bool
    {
        return self::userGenre() === AdminUser::GENRE_AGENT;
    }

    public static function anyAdmin(): bool
    {
        $genre = self::userGenre();
        return $genre === AdminUser::GENRE_ADMIN || $genre === AdminUser::GENRE_SUPER_ADMIN;
    }
}
