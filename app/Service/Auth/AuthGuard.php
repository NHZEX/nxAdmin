<?php
declare(strict_types=1);

namespace app\Service\Auth;

use app\Model\AdminUser as AdminUserModel;
use app\Service\Auth\Access\Gate;
use app\Service\Auth\Contracts\ProviderlSelfCheck;
use app\Service\Auth\Traits\EventHelpers;
use app\Service\Auth\Traits\GuardHelpers;
use think\Config;
use think\Container;
use think\Cookie as CookieJar;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\Session;
use function HZEX\Crypto\decrypt_data;
use function HZEX\Crypto\encrypt_data;

class AuthGuard
{
    use GuardHelpers, EventHelpers;

    /**
     * @var Container
     */
    protected $container;

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
     * Indicates if the logout method has been called.
     *
     * @var bool
     */
    protected $loggedOut = false;

    /**
     * Indicates if the user was authenticated via a recaller cookie.
     *
     * @var bool
     */
    protected $viaRemember = false;

    /**
     * @var AdminUserModel
     */
    protected $user;

    /**
     * @var array
     */
    protected $config = [
        'remember' => [
            'name'   => 'remember',
            'expire' => 604800,
        ],
    ];

    /**
     * AuthGuard constructor.
     * @param Container $container
     * @param Config    $config
     * @param Session   $session
     * @param CookieJar $cookie
     */
    public function __construct(Container $container, Config $config, Session $session, CookieJar $cookie)
    {
        $this->container = $container;
        $this->session = $session;
        $this->cookie  = $cookie;
        $this->config = array_merge($this->config, $config->get('auth', []));
    }

    /**
     * Determine if the current user is authenticated.
     *
     * @return bool
     */
    public function check()
    {
        return null !== $this->user();
    }

    /**
     * Determine if the current user is a guest.
     *
     * @return bool
     */
    public function guest()
    {
        return !$this->check();
    }

    /**
     * @return AdminUserModel|null
     */
    public function user(): ?AdminUserModel
    {
        if ($this->loggedOut) {
            return null;
        }

        if (null !== $this->user) {
            return $this->user;
        }

        $id = $this->session->get($this->getName());

        if (null !== $id && $this->user = $this->retrieveById($id)) {
            $this->triggerAuthenticatedEvent($this->user);
        }

        if (null === $this->user && null !== ($this->user = $this->validRememberToken())) {
            $this->createRememberToken($this->user);
            $this->updateSession($this->user->id);

            $this->triggerLoginEvent($this->user, true);
        }

        return $this->user;
    }

    /**
     * 获取当前经过身份验证的用户的ID
     * Get the ID for the currently authenticated user.
     *
     * @return int|null
     */
    public function id()
    {
        if ($this->loggedOut) {
            return null;
        }

        return $this->session->get($this->getName());
    }

    /**
     * @return Gate
     */
    public function gate()
    {
        return $this->container->make(Gate::class);
    }

    /**
     * @param int $id
     * @return AdminUserModel|null
     */
    protected function retrieveById(int $id): ?AdminUserModel
    {
        try {
            /** @var AdminUserModel $result */
            $result = (new AdminUserModel())->find($id);
            if ($result &&
                $result instanceof ProviderlSelfCheck &&
                !$result->valid($message)
            ) {
                $this->logout();
                $this->setMessage($message);
                return null;
            }
            return $result;
        } catch (DataNotFoundException | ModelNotFoundException | DbException $e) {
            return null;
        }
    }

    public function login(AdminUserModel $user, bool $rememberme = false)
    {
        $this->updateSession($user->id);

        if ($rememberme) {
            $this->ensureRememberTokenIsSet($user);
            $this->createRememberToken($user);
        }

        $this->triggerLoginEvent($user, $rememberme);

        $this->setUser($user);
    }

    /**
     * Update the session with the given ID.
     *
     * @param  string  $id
     * @return void
     */
    protected function updateSession($id)
    {
        $this->session->set($this->getName(), $id);
        $this->session->regenerate();
    }

    public function logout()
    {
        $this->session->delete($this->getName());
        $this->cookie->delete($this->getRecallerName());

        $user = $this->user();

        if (null !== $user && !empty($user->getRememberToken())) {
            $this->cycleRememberToken($user);
        }

        $this->user      = null;
        $this->loggedOut = true;
    }

    protected function ensureRememberTokenIsSet(AdminUserModel $user)
    {
        if (empty($user->getRememberToken())) {
            $this->cycleRememberToken($user);
        }
    }

    /**
     * @param AdminUserModel $user
     * @return void
     */
    protected function createRememberToken(AdminUserModel $user)
    {
        $salt  = env('DEPLOY_SECURITY_SALT');
        $expired = $this->config['remember']['expire'];
        $timeout = time() + $expired;
        $password = hash('crc32', $user->password);
        $token = "{$user->id}|{$user->getRememberToken()}|{$password}|{$timeout}";
        $secret = encrypt_data($token, $salt, 'aes-128-ctr');
        $secret = base64_encode($secret);
        $this->cookie->set($this->getRecallerName(), $secret, [
            'expire' => $expired,
            'httponly' => true,
        ]);
    }

    /**
     * @return AdminUserModel|null
     */
    protected function validRememberToken(): ?AdminUserModel
    {
        $secret = $this->cookie->get($this->getRecallerName());
        if (empty($secret)) {
            return null;
        }
        $salt  = env('DEPLOY_SECURITY_SALT');
        $token = decrypt_data(base64_decode($secret), $salt, 'aes-128-ctr');
        if (empty($token) || 4 > count($remember = explode('|', $token))) {
            return null;
        }
        [$userId, $rememberToken, $pass, $timeout] = $remember;
        if (time() > $timeout) {
            return null;
        }
        $user = $this->retrieveById((int) $userId);
        if (empty($user)
            || $rememberToken !== $user->getRememberToken()
            || $pass !== hash('crc32', $user->password)
        ) {
            return null;
        }
        $this->viaRemember = true;
        return $user;
    }

    protected function cycleRememberToken(AdminUserModel $user)
    {
        $user->updateRememberToken(get_rand_str(16));
    }

    /**
     * @param AdminUserModel $user
     * @return AuthGuard
     */
    public function setUser(AdminUserModel $user)
    {
        $this->user = $user;

        $this->loggedOut = false;

        $this->triggerAuthenticatedEvent($user);

        return $this;
    }

    /**
     * Get a unique identifier for the auth session value.
     *
     * @param string $append
     * @param string $join
     * @return string
     */
    public function getName(string $append = null, string $join = '_')
    {
        return 'login_sess_' . sha1(static::class) . ($append ? ($join . $append) : $append);
    }

    /**
     * Get the name of the cookie used to store the "recaller".
     *
     * @return string
     */
    public function getRecallerName()
    {
        return $this->config['remember']['name'];
    }
}
