<?php
declare(strict_types=1);

namespace app\Service\Auth\Guard;

use app\Service\Auth\Contracts\Authenticatable as AuthenticatableContracts;
use app\Service\Auth\Contracts\StatefulGuard as StatefulGuardContracts;
use app\Service\Auth\Contracts\UserProvider as UserProviderContracts;
use app\Service\Auth\Recaller;
use app\Service\Auth\Traits\GuardEvents;
use app\Service\Auth\Traits\GuardHelpers;
use think\Cookie as CookieJar;
use think\helper\Str;
use think\Request;
use think\Session;

class SessionGuard implements StatefulGuardContracts
{
    use GuardHelpers, GuardEvents;

    /**
     * The name of the Guard. Typically "session".
     *
     * Corresponds to guard name in authentication configuration.
     *
     * @var string
     */
    protected $name;

    /**
     * The user we last attempted to retrieve.
     *
     * @var AuthenticatableContracts
     */
    protected $lastAttempted;

    /**
     * Indicates if the user was authenticated via a recaller cookie.
     *
     * @var bool
     */
    protected $viaRemember = false;

    /**
     * The session used by the guard.
     *
     * @var Session
     */
    protected $session;

    /**
     * The Illuminate cookie creator service.
     *
     * @var CookieJar
     */
    protected $cookie;

    /**
     * @var Request
     */
    protected $request;

    /**
     * Indicates if the logout method has been called.
     *
     * @var bool
     */
    protected $loggedOut = false;

    /**
     * Indicates if a token user retrieval has been attempted.
     *
     * @var bool
     */
    protected $recallAttempted = false;

    /**
     * Create a new authentication guard.
     *
     * @param string                $name
     * @param UserProviderContracts $provider
     * @param Session               $session
     */
    public function __construct(string $name, UserProviderContracts $provider, Session $session)
    {
        $this->name = $name;
        $this->session = $session;
        $this->provider = $provider;
    }

    /**
     * Get the currently authenticated user.
     *
     * @return AuthenticatableContracts
     */
    public function user()
    {
        if ($this->loggedOut) {
            return null;
        }

        if (null !== $this->user) {
            return $this->user;
        }

        // 尝试从会话中获取用户标识
        $id = $this->session->get($this->getName());

        // First we will try to load the user using the identifier in the session if
        // one exists. Otherwise we will check for a "remember me" cookie in this
        // request, and if one exists, attempt to retrieve the user using that.
        if (null !== $id && $this->user = $this->provider->retrieveById($id)) {
            $this->fireAuthenticatedEvent($this->user);
        }

        // If the user is null, but we decrypt a "recaller" cookie we can attempt to
        // pull the user data on that cookie which serves as a remember cookie on
        // the application. once we have a user we can return it to the caller.
        if (null === $this->user && null !== ($recaller = $this->recaller())) {
            $this->user = $this->userFromRecaller($recaller);

            if ($this->user) {
                $this->updateSession($this->user->getAuthIdentifier());

                $this->fireLoginEvent($this->user, true);
            }
        }

        return $this->user;
    }

    /**
     * 通过 "记住我" cookie令牌将用户从存储库中拉出。
     * Pull a user from the repository by its "remember me" cookie token.
     *
     * @param  Recaller  $recaller
     * @return mixed
     */
    protected function userFromRecaller($recaller)
    {
        if (! $recaller->valid() || $this->recallAttempted) {
            return null;
        }

        // If the user is null, but we decrypt a "recaller" cookie we can attempt to
        // pull the user data on that cookie which serves as a remember cookie on
        // the application. Once we have a user we can return it to the caller.
        $this->recallAttempted = true;

        $this->viaRemember = ! is_null($user = $this->provider->retrieveByToken(
            $recaller->id(),
            $recaller->token()
        ));

        return $user;
    }

    /**
     * 获取该请求的解密的记住我cookie令牌
     * Get the decrypted recaller cookie for the request.
     *
     * @return Recaller|null
     */
    protected function recaller()
    {
        if (is_null($this->request)) {
            return null;
        }

        if ($recaller = $this->cookie->get($this->getRecallerName())) {
            return new Recaller($recaller);
        }

        return null;
    }

    /**
     * 获取当前经过身份验证的用户的ID
     * Get the ID for the currently authenticated user.
     *
     * @return int|string|null
     */
    public function id()
    {
        if ($this->loggedOut) {
            return null;
        }

        return $this->user()
            ? $this->user()->getAuthIdentifier()
            : $this->session->get($this->getName());
    }


    /**
     * Log a user into the application without sessions or cookies.
     *
     * @param  array  $credentials
     * @return bool
     */
    public function once(array $credentials = [])
    {
        $this->fireAttemptEvent($credentials);

        if ($this->validate($credentials)) {
            $this->setUser($this->lastAttempted);

            return true;
        }

        return false;
    }

    /**
     * Log the given user ID into the application without sessions or cookies.
     *
     * @param  mixed  $id
     * @return AuthenticatableContracts|false
     */
    public function onceUsingId($id)
    {
        if (! is_null($user = $this->provider->retrieveById($id))) {
            $this->setUser($user);

            return $user;
        }

        return false;
    }

    /**
     * Validate a user's credentials.
     *
     * @param array $credentials
     * @return bool
     */
    public function validate(array $credentials = [])
    {
        $this->lastAttempted = $user = $this->provider->retrieveByCredentials($credentials);

        return $this->hasValidCredentials($user, $credentials);
    }

    /**
     * 尝试使用给定的凭据对用户进行身份验证
     * Attempt to authenticate a user using the given credentials.
     *
     * @param array $credentials
     * @param bool  $remember
     * @return bool
     */
    public function attempt(array $credentials = [], $remember = false)
    {
        $this->fireAttemptEvent($credentials, $remember);

        $this->lastAttempted = $user = $this->provider->retrieveByCredentials($credentials);

        // 验证用户是否允许访问

        // If an implementation of UserInterface was returned, we'll ask the provider
        // to validate the user against the given credentials, and if they are in
        // fact valid we'll log the users into the application and return true.
        if ($this->hasValidCredentials($user, $credentials)) {
            $this->login($user, $remember);

            return true;
        }

        // If the authentication attempt fails we will fire an event so that the user
        // may be notified of any suspicious attempts to access their account from
        // an unrecognized user. A developer may listen to this event as needed.
        $this->fireFailedEvent($user, $credentials);

        return false;
    }

    /**
     * 确定用户是否与凭据匹配
     * Determine if the user matches the credentials.
     *
     * @param  mixed  $user
     * @param  array  $credentials
     * @return bool
     */
    protected function hasValidCredentials($user, $credentials)
    {
        return (null !== $user) && $this->provider->validateCredentials($user, $credentials);
    }

    /**
     * 通过用户ID登录到应用程序中
     * Log the given user ID into the application.
     *
     * @param mixed $id
     * @param bool  $remember
     * @return mixed
     */
    public function loginUsingId($id, $remember = false)
    {
        if (! is_null($user = $this->provider->retrieveById($id))) {
            $this->login($user, $remember);

            return $user;
        }

        return false;
    }

    /**
     * 将用户登录到应用程序
     * Log a user into the application.
     *
     * @param AuthenticatableContracts $user
     * @param bool                     $remember
     * @return void
     */
    public function login(AuthenticatableContracts $user, $remember = false)
    {
        $this->updateSession($user->getAuthIdentifier());

        // If the user should be permanently "remembered" by the application we will
        // queue a permanent cookie that contains the encrypted copy of the user
        // identifier. We will then decrypt this later to retrieve the users.
        if ($remember) {
            $this->ensureRememberTokenIsSet($user);

            $this->queueRecallerCookie($user);
        }

        // If we have an event dispatcher instance set we will fire an event so that
        // any listeners will hook into the authentication events and run actions
        // based on the login and logout events fired from the guard instances.
        $this->fireLoginEvent($user, $remember);

        $this->setUser($user);
    }

    /**
     * 更新用户ID到Session
     * Update the session with the given ID.
     *
     * @param  string  $id
     * @return void
     */
    protected function updateSession($id)
    {
        $this->session->set($this->getName(), $id);

        $this->session->regenerate(true);
    }

    /**
     * 如果尚不存在，请为用户创建一个新的“记住我”令牌。
     * Create a new "remember me" token for the user if one doesn't already exist.
     *
     * @param AuthenticatableContracts $user
     * @return void
     */
    protected function ensureRememberTokenIsSet(AuthenticatableContracts $user)
    {
        if (empty($user->getRememberToken())) {
            $this->cycleRememberToken($user);
        }
    }

    /**
     * 将记住我 Token 保存到 Cookie
     * Queue the recaller cookie into the cookie jar.
     *
     * @param AuthenticatableContracts $user
     * @return void
     */
    protected function queueRecallerCookie(AuthenticatableContracts $user)
    {
        $this->cookie->set(
            $this->getRecallerName(),
            $user->getAuthIdentifier().'|'.$user->getRememberToken().'|'.$user->getAuthPassword()
        );
    }

    /**
     * Log the user out of the application.
     *
     * @return void
     */
    public function logout()
    {
        $user = $this->user();

        $this->clearUserDataFromStorage();

        if (! is_null($this->user) && ! empty($user->getRememberToken())) {
            $this->cycleRememberToken($user);
        }

        // If we have an event dispatcher instance, we can fire off the logout event
        // so any further processing can be done. This allows the developer to be
        // listening for anytime a user signs out of this application manually.
        if (isset($this->events)) {
            // TODO $this->events->dispatch(new Events\Logout($this->name, $user));
        }

        // Once we have fired the logout event we will clear the users out of memory
        // so they are no longer available as the user is no longer considered as
        // being signed into this application and should not be available here.
        $this->user = null;

        $this->loggedOut = true;
    }

    /**
     * 从会话和cookie中删除用户数据
     * Remove the user data from the session and cookies.
     *
     * @return void
     */
    protected function clearUserDataFromStorage()
    {
        $this->session->delete($this->getName());

        if (! is_null($this->recaller())) {
            $this->cookie->delete($this->getRecallerName());
        }
    }

    /**
     * 刷新记住我令牌
     * Refresh the "remember me" token for the user.
     *
     * @param AuthenticatableContracts $user
     * @return void
     */
    protected function cycleRememberToken(AuthenticatableContracts $user)
    {
        $user->setRememberToken($token = Str::random(60));

        $this->provider->updateRememberToken($user, $token);
    }

    /**
     * Log the user out of the application on their current device only.
     *
     * @return void
     */
    public function logoutCurrentDevice()
    {
        $user = $this->user();

        $this->clearUserDataFromStorage();

        // If we have an event dispatcher instance, we can fire off the logout event
        // so any further processing can be done. This allows the developer to be
        // listening for anytime a user signs out of this application manually.
        if (isset($this->events)) {
            // TODO $this->events->dispatch(new Events\CurrentDeviceLogout($this->name, $user));
        }

        // Once we have fired the logout event we will clear the users out of memory
        // so they are no longer available as the user is no longer considered as
        // being signed into this application and should not be available here.
        $this->user = null;

        $this->loggedOut = true;
    }

    /**
     * Invalidate other sessions for the current user.
     *
     * The application must be using the AuthenticateSession middleware.
     *
     * @param  string  $password
     * @param  string  $attribute
     */
    public function logoutOtherDevices($password, $attribute = 'password')
    {
        // TODO 未实现
    }

    /**
     * Get the last user we attempted to authenticate.
     *
     * @return AuthenticatableContracts
     */
    public function getLastAttempted()
    {
        return $this->lastAttempted;
    }

    /**
     * Get a unique identifier for the auth session value.
     *
     * @return string
     */
    public function getName()
    {
        return 'login_' . $this->name . '_' . sha1(static::class);
    }

    /**
     * Get the name of the cookie used to store the "recaller".
     *
     * @return string
     */
    public function getRecallerName()
    {
        return 'remember_' . $this->name . '_' . sha1(static::class);
    }

    /**
     * Determine if the user was authenticated via "remember me" cookie.
     *
     * @return bool
     */
    public function viaRemember()
    {
        return $this->viaRemember;
    }

    /**
     * Set the cookie creator instance used by the guard.
     *
     * @param  CookieJar  $cookie
     * @return void
     */
    public function setCookieJar(CookieJar $cookie)
    {
        $this->cookie = $cookie;
    }

    /**
     * Return the currently cached user.
     *
     * @return AuthenticatableContracts|null
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set the current user.
     *
     * @param AuthenticatableContracts $user
     * @return $this
     */
    public function setUser(AuthenticatableContracts $user)
    {
        $this->user = $user;

        $this->loggedOut = false;

        $this->fireAuthenticatedEvent($user);

        return $this;
    }

    /**
     * Set the current request instance.
     *
     * @param  Request  $request
     * @return $this
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;

        return $this;
    }
}
