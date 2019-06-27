<?php
/**
 * Created by PhpStorm.
 * User: NHZEXG
 * Date: 2019/2/21
 * Time: 15:29
 */

namespace app\Facade;

use app\Model\AdminUser as AdminUserModel;
use app\Service\WebConv\WebConv as WebConvService;

/**
 * Class WebConv
 * @package app\Facade
 * @method WebConvService instance() static
 * @method WebConvService createSession(AdminUserModel $user, bool $rememberme = false) static
 * @method AdminUserModel decodeRememberToken(?string $value = null) static
 * @method string getErrorMessage() static
 * @method bool verify(bool $force = false) static
 * @method ?bool lookVerify() static
 * @method AdminUserModel getConvUser(bool $force = false) static
 * @method bool isSuperAdmin() static
 * @method int getUserId() static
 * @method int getUserGenre() static
 * @method int getRoleId() static
 * @method int getLoginTime() static
 * @method void destroy(bool $destroy_remember = false) static
 */
class WebConv extends Base
{
    protected static function getFacadeClass()
    {
        return 'webconv';
    }
}
