<?php
declare(strict_types=1);

namespace app\Service\Auth\Guard;

use app\Service\Auth\Contracts\Authenticatable;
use app\Service\Auth\Contracts\StatefulGuard;
use app\Service\Auth\Contracts\UserProvider;

class SessionGuard implements StatefulGuard
{
    /**
     * @var UserProvider
     */
    protected $provider;

    /**
     * Create a new authentication guard.
     *
     * @param UserProvider $provider
     */
    public function __construct(UserProvider $provider = null)
    {
        $this->provider = $provider;
    }

    /**
     * Determine if the current user is authenticated.
     *
     * @return bool
     */
    public function check()
    {
        // TODO: Implement check() method.
    }

    /**
     * Determine if the current user is a guest.
     *
     * @return bool
     */
    public function guest()
    {
        // TODO: Implement guest() method.
    }

    /**
     * Get the currently authenticated user.
     *
     * @return mixed
     */
    public function user()
    {
        // TODO: Implement user() method.
    }

    /**
     * Get the ID for the currently authenticated user.
     *
     * @return int|string|null
     */
    public function id()
    {
        // TODO: Implement id() method.
    }

    /**
     * Validate a user's credentials.
     *
     * @param array $credentials
     * @return bool
     */
    public function validate(array $credentials = [])
    {
        // TODO: Implement validate() method.
    }

    /**
     * Set the current user.
     *
     * @param mixed $user
     * @return void
     */
    public function setUser($user)
    {
        // TODO: Implement setUser() method.
    }

    /**
     * Attempt to authenticate a user using the given credentials.
     *
     * @param array $credentials
     * @param bool  $remember
     * @return bool
     */
    public function attempt(array $credentials = [], $remember = false)
    {
        // TODO 准备登录事件

        $user = $this->provider->retrieveByCredentials($credentials);

        if ($this->hasValidCredentials($user, $credentials)) {
            $this->login($user, $remember);

            return true;
        }

        // TODO 登录失败事件

        return false;
    }

    /**
     * Log a user into the application without sessions or cookies.
     *
     * @param array $credentials
     * @return bool
     */
    public function once(array $credentials = [])
    {
        // TODO: Implement once() method.
    }

    /**
     * Log a user into the application.
     *
     * @param Authenticatable $user
     * @param bool  $remember
     * @return void
     */
    public function login(Authenticatable $user, $remember = false)
    {
        dump($user);
        // TODO: Implement login() method.
    }

    /**
     * Log the given user ID into the application.
     *
     * @param mixed $id
     * @param bool  $remember
     * @return mixed
     */
    public function loginUsingId($id, $remember = false)
    {
        // TODO: Implement loginUsingId() method.
    }

    /**
     * Log the given user ID into the application without sessions or cookies.
     *
     * @param mixed $id
     * @return bool
     */
    public function onceUsingId($id)
    {
        // TODO: Implement onceUsingId() method.
    }

    /**
     * Determine if the user was authenticated via "remember me" cookie.
     *
     * @return bool
     */
    public function viaRemember()
    {
        // TODO: Implement viaRemember() method.
    }

    /**
     * Log the user out of the application.
     *
     * @return void
     */
    public function logout()
    {
        // TODO: Implement logout() method.
    }

    /**
     * Determine if the user matches the credentials.
     *
     * @param  mixed  $user
     * @param  array  $credentials
     * @return bool
     */
    protected function hasValidCredentials($user, $credentials)
    {
        return ! is_null($user) && $this->provider->validateCredentials($user, $credentials);
    }
}
