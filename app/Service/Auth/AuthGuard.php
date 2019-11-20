<?php
declare(strict_types=1);

namespace app\Service\Auth;

use app\Logic\AdminRole;
use app\Model\AdminUser as AdminUserModel;
use app\Server\DeployInfo;
use app\Service\Auth\Traits\GuardHelpers;
use think\Cookie as CookieJar;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\Session;
use function HZEX\Crypto\decrypt_data;
use function HZEX\Crypto\encrypt_data;

class AuthGuard
{
    use GuardHelpers;

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
    protected $permissions;

    public function __construct(Session $session, CookieJar $cookie)
    {
        $this->session = $session;
        $this->cookie  = $cookie;
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
            $this->session->set($this->getName('data.access_time'), time());
        }

        if (null === $this->user && null !== ($this->user = $this->validRememberToken())) {
            $this->createRememberToken($this->user);
            $this->updateSession($this->user->id);
        }

        return $this->user;
    }

    /**
     * @return array|null
     */
    public function permissions(): ?array
    {
        if ($this->loggedOut) {
            return null;
        }
        if (!$this->user()) {
            return null;
        }
        if (!$this->permissions) {
            $this->permissions = AdminRole::queryPermission($this->user()->role_id);
        }
        return $this->permissions;
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
     * @param $name
     * @return bool
     */
    public function can($name): bool
    {
        if ($this->loggedOut) {
            return false;
        }
        $permissions = $this->permissions();
        return $permissions && isset($permissions[$name]);
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
        $salt  = DeployInfo::getSecuritySalt();
        $expired = 604800; // 7 day
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
        $salt  = DeployInfo::getSecuritySalt();
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
     */
    public function setUser(AdminUserModel $user): void
    {
        $this->user = $user;
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
        return 'remember_' . sha1(static::class);
    }
}
