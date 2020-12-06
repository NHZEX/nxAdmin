<?php

namespace app\Service\Auth;

use app\Model\AdminUser;

/**
 * Class AuthManager
 * @package app\Service\Auth
 * @method AdminUser|null user() static
 */
class AuthManager extends \Zxin\Think\Auth\AuthManager
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
