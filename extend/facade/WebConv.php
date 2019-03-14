<?php
/**
 * Created by PhpStorm.
 * User: NHZEXG
 * Date: 2019/2/21
 * Time: 15:29
 */

namespace facade;

use app\model\AdminUser as AdminUserModel;
use app\server\WebConv as WebConvServer;

/**
 * Class WebConv
 * @package facade
 * @method WebConvServer getSelf() static
 * @method bool verify() static
 * @method string getErrorMessage() static
 * @method WebConvServer createSession(AdminUserModel $user, bool $rememberme = false) static
 * @method AdminUserModel decodeRememberToken(?string $value) static
 * @method AdminUserModel getAdminUser(bool $force = false) static
 * @method void destroy(bool $destroy_remember = false) static
 * @method string getCookieLastlove() static
 */
class WebConv extends Base
{
    protected static function getFacadeClass()
    {
        return 'web_conv';
    }
}
