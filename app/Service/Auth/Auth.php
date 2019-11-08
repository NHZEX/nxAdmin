<?php
declare(strict_types=1);

namespace app\Service;

use app\Model\AdminUser;
use app\Service\Auth\Contracts\UserProvider;
use app\Service\Auth\Guard\SessionGuard;
use app\Service\Auth\PasswordHasher;
use app\Service\Auth\ThinkOrmUserProvider;
use think\App;
use think\Manager;

/**
 * Class Auth
 * @package app\Service
 * @mixin SessionGuard
 */
class Auth extends Manager
{
    protected $namespace = '\\app\\Service\\Auth\\Guard\\';

    public function __construct(App $app)
    {
        parent::__construct($app);

        // TODO 临时测试
        $app->bind(UserProvider::class, function () {
            return new ThinkOrmUserProvider(new PasswordHasher(), AdminUser::class);
        });
    }

    /**
     * 默认驱动
     * @return string|null
     */
    public function getDefaultDriver()
    {
        return 'SessionGuard';
    }
}
