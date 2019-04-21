<?php
/**
 * Created by PhpStorm.
 * User: NHZEXG
 * Date: 2018/10/22
 * Time: 12:17
 */

namespace app\Server;

use app\Exception\BusinessResult as BusinessResultSuccess;
use app\Model\AdminRole;
use app\Model\AdminUser;
use app\Model\AdminUser as AdminUserModel;
use ErrorException;
use Hashids\Hashids;
use Serializable;
use think\App;
use think\Config;
use think\Cookie;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\DbException;
use Tp\Session;
use function serialize;
use function unserialize;

/**
 * Class AdminConv
 * @package app\common\server
 * @internal 不要直接使用该类
 *
 * @property int $sess_user_id
 * @property int $sess_user_genre
 * @property int $sess_user_status
 * @property int $sess_role_id
 * @property int $sess_role_time
 * @property int $sess_update_time
 * @property int $sess_login_time
 * @property string $sess_user_feature
 * @property int $sess_user_agent
 */
class WebConv implements Serializable
{
    /** @var App */
    private $app;
    /** @var Session */
    private $session2;
    /** @var Cookie */
    private $cookie;

    /** @var string 错误信息 */
    private $errorMessage;
    /** @var string 会话ID */
    private $sessionId;
    /** @var array 会话数据 */
    private $convAdminInfo = [
        'user_id' => null,
        'user_genre' => null,
        'user_status' => null,
        'role_id' => null,
        'role_time' => null,
        'update_time' => null,
        'login_time' => null,
        'user_feature' => null,
        'user_agent' => null,
    ];
    /** @var bool 验证结果 */
    private $verifyResult;
    /** @var AdminUser */
    private $user;

    /** @var int 会话超时时间 */
    private $sessTimeOut = 7200;

    const COOKIE_LASTLOVE = 'lastlove';

    const CONV_CREATE_TIME = 'create_time';
    const CONV_ADMIN_INFO = 'conv_info';
    const CONV_ADMIN_TOKEN = 'conv_token';
    const CONV_COMMON_KEY = 'common_key';
    const CONV_ACCESS_TIME = 'access_time';

    const SESS_REFRESH_TIME_OUT = 10800; // 每3小时刷新一次 SESSION_ID

    /**
     * 返回模型的错误信息
     * @access public
     * @return string|array
     */
    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }

    /**
     * String representation of object
     * @link  https://php.net/manual/en/serializable.serialize.php
     * @return string the string representation of the object or null
     * @since 5.1.0
     */
    public function serialize()
    {
        return serialize([
            'sessionId' => $this->sessionId,
            'convAdminInfo' => $this->convAdminInfo,
            'verifyResult' => $this->verifyResult,
        ]);
    }

    /**
     * Constructs the object
     * @link  https://php.net/manual/en/serializable.unserialize.php
     * @param string $serialized <p>
     *                           The string representation of the object.
     *                           </p>
     * @return void
     * @since 5.1.0
     */
    public function unserialize($serialized)
    {
        $this->app = App::getInstance();
        $this->app->bindTo(self::class, $this);
        $this->cookie = $this->app->cookie;
        $this->session2 = $this->app->make(Session::class);
        $this->sessTimeOut = $this->app->config->get('session.expire', 7200);

        $data = unserialize($serialized);

        foreach ($data as $key => $value) {
            $this->$key = $value;
        }

        $this->app->log->alert($serialized);
        $this->app->log->alert($data);
    }

    /**
     * AdminConv constructor.
     * @param App     $app
     * @param Session $session2
     * @param Cookie  $cookie
     * @param Config  $config
     */
    public function __construct(App $app, Session $session2, Cookie $cookie, Config $config)
    {
        $this->app = $app;
        $this->cookie = $cookie;
        $this->session2 = $session2;

        $this->sessTimeOut = $config->get('session.expire', 7200);

        // 初始化Session
        $session2->init();

        $this->sessionId = $session2->getId();

        $this->loadConvInfo();
    }

    /**
     * 载入会话信息
     */
    private function loadConvInfo(): void
    {
        // 加载会话数据
        $info = $this->session2->get(self::CONV_ADMIN_INFO);
        $info && $this->convAdminInfo = array_merge($this->convAdminInfo, $info);
    }

    /**
     * @param $name
     * @return mixed
     * @throws ErrorException
     */
    public function __get($name)
    {
        if (0 === strpos($name, 'sess')) {
            return $this->convAdminInfo[substr($name, 5)];
        }
        throw new ErrorException('Undefined property: ' . __CLASS__ . '::' . $name);
    }

    /**
     * @param $name
     * @param $value
     * @throws ErrorException
     */
    public function __set($name, $value)
    {
        if (0 === strpos($name, 'sess')) {
            $name = substr($name, 5);
            $this->convAdminInfo[$name] = $value;
            return;
        }
        throw new ErrorException('Undefined property: ' . __CLASS__ . '::' . $name);
    }

    /**
     * @param AdminUserModel $user
     * @param bool           $rememberme
     * @return static
     */
    public function createSession(AdminUserModel $user, bool $rememberme = false): self
    {
        // 获取特征串 (必然重复/只做辅助识别)
        $user_agent = $this->app->request->header('User-Agent');

        // 用户特征
        $user_feature = self::generateUserFeature($user);
        $user_role = $user->role_id ? $user->role : null;
        // 会话信息
        $conv_info = [
            'user_id' => $user->id,
            'user_genre' => $user->genre,
            'user_status' => $user->status,
            'role_id' => $user->role_id,
            'role_time' => $user_role ? $user_role->update_time : 0,
            'login_time' => $user->last_login_time,
            'user_feature' => $user_feature,
            'user_agent' => crc32($user_agent),
        ];

        // 记住登录状态
        if ($rememberme) {
            $rememberme_out_time = 604800; // 7 day
            $token = $this->createRememberToken($user, $user_agent, $rememberme_out_time);
            $this->cookie->set(self::COOKIE_LASTLOVE, $token, [
                'expire' => $rememberme_out_time,
                'httponly' => true,
            ]);
        }

        // 设置
        $this->setCreateTime();
        $this->flushExpired();
        $this->session2->set(self::CONV_ADMIN_INFO, $conv_info);
        $this->session2->set(self::CONV_COMMON_KEY, get_rand_str(16));

        $this->sessionId = $this->session2->getId();
        $this->loadConvInfo();
        return $this;
    }

    /**
     * 生成记住令牌
     * @param AdminUserModel $user
     * @param string $user_agent
     * @param int $expire
     * @return string
     */
    public function createRememberToken(AdminUserModel $user, string $user_agent, int $expire): string
    {
        $salt = DeployInfo::getSecuritySalt();
        // 用户特征
        $user_feature = self::generateUserFeature($user);
        // 签名
        $sign_info = [
            'user_feature' => $user_feature,
            'user_agent' => crc32($user_agent)
        ];
        $sign = array_sign($sign_info, 'sha1', $salt . $user->remember);

        $Hashids = new Hashids($salt . $sign, 8);
        $index = $Hashids->encode($user->id, time() + $expire);

        $token_token = "{$index}.$sign";
        return $token_token;
    }

    /**
     * 解码记住令牌
     * @param string|null $value
     * @return AdminUserModel|null AdminUserModel 用户对象
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     * @throws DbException
     */
    public function decodeRememberToken(?string $value = null): ?AdminUserModel
    {
        try {
            if (!$value) {
                $value = $this->cookie->get(self::COOKIE_LASTLOVE);
            }
            $lastlove = explode('.', $value);
            if (count($lastlove) !== 2) {
                throw new BusinessResultSuccess('记住令牌不合法');
            }
            [$index, $sign] = $lastlove;

            $salt = DeployInfo::getSecuritySalt();

            $Hashids = new Hashids($salt . $sign, 8);
            $index_arr = $Hashids->decode($index);
            if (count($index_arr) !== 2) {
                throw new BusinessResultSuccess('令牌头无法识别');
            }
            [$user_id, $expire] = $index_arr;

            if (time() > $expire) {
                throw new BusinessResultSuccess('记住令牌过期');
            }

            /** @var AdminUserModel $user */
            $user = (new AdminUserModel())->wherePk($user_id)->find();
            if (false === $user instanceof AdminUserModel) {
                throw new BusinessResultSuccess('用户不存在');
            }
            if (AdminUserModel::STATUS_NORMAL !== $user->status) {
                throw new BusinessResultSuccess("账号状态：{$user->status_desc}");
            }
            // 用户特征
            $user_feature = self::generateUserFeature($user);
            // 获取访问特征串 (必然重复/只做辅助识别)
            $user_agent = request()->header('User-Agent');
            // 签名
            $sign_info = [
                'user_feature' => $user_feature,
                'user_agent' => crc32($user_agent)
            ];
            $sign_test = array_sign($sign_info, 'sha1', $salt . $user->remember);
            if ($sign !== $sign_test) {
                throw new BusinessResultSuccess('数据一致性失败');
            }
        } catch (BusinessResultSuccess $result) {
            $this->cookie->delete(self::COOKIE_LASTLOVE);
            return null;
        }

        return $user;
    }

    /**
     * 生成用户数据特征码
     * @param AdminUserModel $user
     * @return string
     */
    protected static function generateUserFeature(AdminUserModel $user): string
    {
        $feature = [
            $user->id, $user->genre, $user->status, $user->password
        ];
        return md5(join('|', $feature));
    }

    /**
     * @noinspection PhpDocMissingThrowsInspection
     * @param bool $force
     * @return AdminUser
     */
    public function getAdminUser(bool $force = false)
    {
        if ($force || false === $this->user instanceof AdminUser) {
            /** @noinspection PhpUnhandledExceptionInspection */
            $this->user = (new AdminUser())->wherePk($this->sess_user_id)->find();
        }
        return $this->user;
    }

    /**
     * @return bool
     */
    public function isSuperAdmin(): bool
    {
        return $this->sess_user_genre === AdminUser::GENRE_SUPER_ADMIN;
    }

    /**
     * @return string|null
     */
    public function getSessionId() :?string
    {
        return $this->sessionId;
    }

    /**
     * 验证会话
     * @param bool $force
     * @return bool
     */
    public function verify(bool $force = false)
    {
        if (null !== $this->verifyResult && !$force) {
            return $this->verifyResult;
        }
        $curr_time = time();
        try {
            if (!$this->sessionId || empty($this->session2->get())) {
                throw new BusinessResultSuccess('会话不存在');
            }
            if ($curr_time > $this->session2->get(self::CONV_ACCESS_TIME)) {
                throw new BusinessResultSuccess('会话过期');
            }
            $user_agent = request()->header('User-Agent');
            if ($this->sess_user_agent !== crc32($user_agent)) {
                throw new BusinessResultSuccess('会话环境不一致');
            }
            $user = $this->getAdminUser();
            if ($this->sess_login_time !== $user->last_login_time ||
                self::generateUserFeature($user) !== $this->sess_user_feature
            ) {
                throw new BusinessResultSuccess('用户状态发生更变');
            }
            if (AdminUser::STATUS_NORMAL !== $user->status || $this->sess_user_status !== $user->status) {
                throw new BusinessResultSuccess("用户状态：{$user->status_desc}");
            }
            if ($user->role_id !== $this->sess_role_id
                || ($user->role_id && $user->role && $user->role->update_time !== $this->sess_role_time)
            ) {
                throw new BusinessResultSuccess('角色状态发生更变');
            }
            if ($user->role_id && $user->role && AdminRole::STATUS_NORMAL !== $user->role->status) {
                throw new BusinessResultSuccess("角色状态：{$user->role->status_desc}");
            }
        } catch (BusinessResultSuccess $result) {
            $this->destroy();
            $this->errorMessage = $result->getMessage();
            return $this->verifyResult = false;
        }

        if ($curr_time > $this->session2->get(self::CONV_CREATE_TIME)) {
            // 旧会话延迟10秒失效
            $this->flushExpired(10);
            // 刷新会话ID
            $this->session2->regenerate();
            // 更新会话信息
            $this->sessionId = $this->session2->getId();
            // 设置创建时间
            $this->setCreateTime();
        }

        // 会话续期
        $this->flushExpired();

        return $this->verifyResult = true;
    }

    /**
     * 会话续期
     * @param int|null $out_time
     * @author NHZEXG
     */
    public function flushExpired(?int $out_time = null)
    {
        $this->session2->set(self::CONV_ACCESS_TIME, time() + ($out_time ?? $this->sessTimeOut));
    }

    /**
     * 设置创建时间
     * @author NHZEXG
     */
    public function setCreateTime()
    {
        $this->session2->set(self::CONV_CREATE_TIME, time() + self::SESS_REFRESH_TIME_OUT);
    }

    /**
     * 销毁会话/
     * @param bool $destroy_remember 销毁记住登陆
     */
    public function destroy(bool $destroy_remember = false)
    {
        if ($destroy_remember) {
            $user = $this->getAdminUser();
            if ($user instanceof AdminUser) {
                $user->remember = get_rand_str(16);
                $user->save();
            }
            $this->cookie->delete(self::COOKIE_LASTLOVE);
        }
        // 销毁 Session
        $this->session2->destroy();
    }
}
