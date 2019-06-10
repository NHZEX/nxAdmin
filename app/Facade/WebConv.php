<?php
/**
 * Created by PhpStorm.
 * User: NHZEXG
 * Date: 2019/2/21
 * Time: 15:29
 */

namespace app\Facade;

use app\Model\AdminUser as AdminUserModel;
use app\Server\WebConv as WebConvServer;

/**
 * Class WebConv
 * @package app\Facade
 * @method WebConvServer getSelf() static
 * @method WebConvServer instance() static
 * @method ?bool lookVerify() static
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
