<?php

declare(strict_types=1);

namespace app\Service\Auth;

use Composer\Pcre\Preg;
use Zxin\Think\Auth\Permission;

class AuthUtils
{
    public static function permissionsMatch(array $patterns): array
    {
        $patterns = array_map(function ($str) {
            $is = str_ends_with($str, '.');
            $str = preg_quote($is ? substr($str, 0, -1) : $str, '#');
            if ($is) {
                $str .= '(\.\S+)?';
            }

            return $str;
        }, $patterns);
        $regular = \sprintf('#^(%s)$#m', implode('|', $patterns));
        $permission = [];
        foreach (Permission::getInstance()->allPermission() as $key => $_) {
            if (Preg::isMatch($regular, $key)) {
                $permission[] = $key;
            }
        }

        return $permission;
    }
}
