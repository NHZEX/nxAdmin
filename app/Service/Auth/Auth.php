<?php
declare(strict_types=1);

namespace app\Service;

use app\Service\Auth\Guard\SessionGuard;
use app\Service\Auth\PasswordHasher;
use app\Service\Auth\ThinkOrmUserProvider;
use InvalidArgumentException;
use think\helper\Arr;
use think\Manager;

/**
 * Class Auth
 * @package app\Service
 * @mixin SessionGuard
 */
class Auth extends Manager
{
    protected $namespace = '\\app\\Service\\Auth\\Guard\\';

    /**
     * 默认驱动
     * @return string|null
     */
    public function getDefaultDriver()
    {
        return $this->app->config->get('auth.defaults.guard');
    }

    /**
     * 获取鉴权配置
     * @access public
     * @param null|string $name    名称
     * @param mixed       $default 默认值
     * @return mixed
     */
    public function getConfig(string $name = null, $default = null)
    {
        if (!is_null($name)) {
            return $this->app->config->get('auth.' . $name, $default);
        }

        return $this->app->config->get('auth');
    }

    /**
     * 获取守卫配置
     * @param string $store
     * @param string $name
     * @param null   $default
     * @return array
     */
    public function getGuardConfig(string $store, string $name = null, $default = null)
    {
        if ($config = $this->getConfig("guards.{$store}")) {
            return Arr::get($config, $name, $default);
        }

        throw new InvalidArgumentException("Suard [$store] not found.");
    }

    /**
     * 获取守卫配置
     * @param string $store
     * @param string $name
     * @param null   $default
     * @return array|string
     */
    public function getProviderConfig(string $store, string $name = null, $default = null)
    {
        if ($config = $this->getConfig("providers.{$store}")) {
            return Arr::get($config, $name, $default);
        }

        throw new InvalidArgumentException("Provider [$store] not found.");
    }

    /**
     * 解决驱动类型
     *
     * @param string $name
     * @return array|mixed
     */
    protected function resolveType(string $name)
    {
        return $this->getGuardConfig($name, 'driver', 'session');
    }

    /**
     * 解决驱动配置
     * @param string $name
     * @return array|mixed
     */
    protected function resolveConfig(string $name)
    {
        return $this->getGuardConfig($name);
    }

    /**
     * 解决驱动参数
     * @param $name
     * @return array
     */
    protected function resolveParams($name): array
    {
        return [$name, $this->resolveConfig($name)];
    }

    /**
     * Create a session based authentication guard.
     *
     * @param  string  $name
     * @param  array  $config
     * @return SessionGuard
     */
    public function createSessionDriver($name, $config)
    {
        $provider = new ThinkOrmUserProvider(
            new PasswordHasher(),
            $this->getProviderConfig($config['provider'], 'model')
        );

        $guard = new SessionGuard($name, $provider, $this->app->session);

        if (method_exists($guard, 'setCookieJar')) {
            $guard->setCookieJar($this->app->cookie);
        }

        return $guard;
    }
}
