<?php
/**
 * Created by PhpStorm.
 * User: NHZEXG
 * Date: 2019/2/21
 * Time: 15:29
 */

namespace app\facade;

use app\model\AdminUser as AdminUserModel;
use app\server\WebConv as WebConvServer;

/**
 * Class WebConv
 * @package facade
 * @method WebConvServer getSelf() static
 * @method bool verify(bool $force = false) static
 * @method string getErrorMessage() static
 * @method WebConvServer createSession(AdminUserModel $user, bool $rememberme = false) static
 * @method AdminUserModel decodeRememberToken(?string $value = null) static
 * @method AdminUserModel getAdminUser(bool $force = false) static
 * @method bool isSuperAdmin() static
 * @method void destroy(bool $destroy_remember = false) static
 */
class WebConv extends Base
{
    protected static function getFacadeClass()
    {
        return WebConvServer::class;
    }
}
