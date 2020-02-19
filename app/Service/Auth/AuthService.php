<?php
declare(strict_types=1);

namespace app\Service\Auth;

use app\Model\AdminUser;
use app\Service\Auth\Access\Gate;
use app\Service\Auth\Middleware\Authorize;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\Reader;
use HZEX\Blade\Register;
use think\App;
use think\Service;

class AuthService extends Service
{
    /**
     * @var Reader
     */
    protected $reader;

    /**
     */
    public function register()
    {
        // 注册到容器
        $this->app->bind('auth', AuthGuard::class);
        // 注册鉴权中间件
        $this->app->middleware->add(Authorize::class, 'route');
        // 注册鉴权类
        $this->registerAccessGate();
        $this->registeBladeExtension();

        // TODO: this method is deprecated and will be removed in doctrine/annotations 2.0
        AnnotationRegistry::registerLoader('class_exists');
    }

    public function boot()
    {
    }

    protected function registerAccessGate()
    {
        $this->app->bind('auth.gate', Gate::class);
        $this->app->bind(Gate::class, function (App $app) {
            $gate = (new Gate($app, function () use ($app) {
                return $app->make('auth')->user();
            }));
            $this->registerUriGateAbilities($gate);
            return $gate;
        });
    }

    protected function registerUriGateAbilities(Gate $gate)
    {
        $gate->define(Permission::class, function (AdminUser $user, string $uri) {
            return isset($user->permissions()[$uri]);
        });
        $gate->before(function (AdminUser $user, string $uri) use ($gate) {
            if (!$gate->has($uri) && Permission::getInstance()->contain($uri)) {
                $uri = Permission::getInstance()->getPermissionByFeature($uri) ?? $uri;
                return $user->allowPermission($uri);
            }
            return null;
        });
    }

    protected function registeBladeExtension()
    {
        /** @var Register $register */
        $register = $this->app->make(Register::class);
        $register->directive('allows', function ($parameter) {
            return "<?php echo app('auth')->gate()->allows({$parameter}) ? 'true' : 'false' ?>";
        });
        $register->directive('denies', function ($parameter) {
            return "<?php echo app('auth')->gate()->denies({$parameter}) ? 'true' : 'false' ?>";
        });
        $register->directive('check', function ($parameter) {
            return "<?php echo app('auth')->gate()->check({$parameter}) ? 'true' : 'false' ?>";
        });
        $register->directive('any', function ($parameter) {
            return "<?php echo app('auth')->gate()->any({$parameter}) ? 'true' : 'false' ?>";
        });
    }
}
