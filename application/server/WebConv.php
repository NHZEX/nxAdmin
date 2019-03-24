<?php
/**
 * Created by PhpStorm.
 * User: NHZEXG
 * Date: 2018/10/22
 * Time: 12:17
 */

namespace app\server;

use app\exception\BusinessResult as BusinessResultSuccess;
use app\model\AdminRole;
use app\model\AdminUser;
use app\model\AdminUser as AdminUserModel;
use facade\Session2;
use Hashids\Hashids;
use think\facade\App;
use think\facade\Cookie;

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
 * @property int $sess_access_time
 * @property int $sess_user_agent
 */
class WebConv
{
    // 错误信息
    protected $errorMessage;
    /** @var string */
    private $convCookieName;
    /** @var string */
    private $sessionId;
    /** @var string */
    private $sessionToken;
    /** @var AdminUser */
    private $user;

    /** @var bool 验证结果 */
    protected $verifyResult;

    private $convAdminInfo = [
        'user_id' => null,
        'user_genre' => null,
        'user_status' => null,
        'role_id' => null,
        'role_time' => null,
        'update_time' => null,
        'login_time' => null,
        'user_feature' => null,
        'access_time' => null,
        'user_agent' => null,
    ];

    const COOKIE_LASTLOVE = 'lastlove';
    const COOKIE_CONV_TOKEN = 'token';

    const CONV_ADMIN_INFO = 'conv_admin_info';
    const CONV_ADMIN_TOKEN = 'conv_admin_token';
    const CONV_COMMON_KEY = 'common_key';

    const CONV_TIME_OUT = 7200;

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
     * 反序列化的魔术方法
     * User: Johnson
     */
    public function __wakeup()
    {
        App::set('web_conv', $this);
        $this->verifyResult = true;
    }

    /**
     * AdminConv constructor.
     * @internal 不要直接使用该类
     */
    public function __construct()
    {
        // 初始化Session
        Session2::init();

        $this->convCookieName = Session2::getName();
        $this->sessionId = Session2::getId();
        $this->sessionToken = Cookie::get('token');

        $this->loadConvInfo();
    }

    /**
     * 载入会话信息
     */
    private function loadConvInfo(): void
    {
        // 加载会话数据
        $info = Session2::get(self::CONV_ADMIN_INFO);
        $info && $this->convAdminInfo = array_merge($this->convAdminInfo, $info);
    }

    /**
     * 保存会话信息
     */
    private function saveConvInfo(): void
    {
        Session2::set(self::CONV_ADMIN_INFO, $this->convAdminInfo);
    }

    /**
     * @param $name
     * @return mixed
     * @throws \ErrorException
     */
    public function __get($name)
    {
        if (0 === strpos($name, 'sess')) {
            return $this->convAdminInfo[substr($name, 5)];
        }
        throw new \ErrorException('Undefined property: '.__CLASS__."::{$name}");
    }

    /**
     * @param $name
     * @param $value
     * @throws \ErrorException
     */
    public function __set($name, $value)
    {
        if (0 === strpos($name, 'sess')) {
            $name = substr($name, 5);
            $this->convAdminInfo[$name] = $value;
            return;
        }
        throw new \ErrorException('Undefined property: '.__CLASS__."::{$name}");
    }

    /**
     * @return string
     */
    public static function getCookieLastlove(): ?string
    {
        return Cookie::get(self::COOKIE_LASTLOVE);
    }

    /**
     * @param AdminUserModel $user
     * @param bool           $rememberme
     * @return static
     */
    public static function createSession(AdminUserModel $user, bool $rememberme = false): self
    {
        // 创建实例
        $that = \facade\WebConv::getSelf();

        // 获取特征串 (必然重复/只做辅助识别)
        $user_agent = request()->header('User-Agent');

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
            'access_time' => time() + self::CONV_TIME_OUT,
            'user_agent' => crc32($user_agent),
        ];

        // 记住登录状态
        if ($rememberme) {
            $rememberme_out_time = 604800;
            $token = $that->createRememberToken($user, $user_agent, $rememberme_out_time);
            Cookie::set(self::COOKIE_LASTLOVE, $token, [
                'expire' => $rememberme_out_time,
                'httponly' => true,
            ]); // 7 day
        }

        // 生成访问令牌
        $that->sessionToken = get_rand_str(16);

        // 设置
        Session2::set(self::CONV_ADMIN_INFO, $conv_info);
        Session2::set(self::CONV_ADMIN_TOKEN, $that->sessionToken);
        Session2::set(self::CONV_COMMON_KEY, get_rand_str(16));
        Cookie::set(self::COOKIE_CONV_TOKEN, $that->sessionToken);

        // TODO 需要统一获取
        $that->sessionId = Session2::getId();

        $that->loadConvInfo();
        return $that;
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
        $salt = Deploy::getSecuritySalt();
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
     * @param string $value
     * @return AdminUserModel|null AdminUserModel 用户对象
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function decodeRememberToken(?string $value): ?AdminUserModel
    {
        try {
            if (!$value) {
                throw new BusinessResultSuccess('记住令牌不存在');
            }
            $lastlove = explode('.', $value);
            if (count($lastlove) !== 2) {
                throw new BusinessResultSuccess('记住令牌不合法');
            }
            [$index, $sign] = $lastlove;

            $salt = Deploy::getSecuritySalt();

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
            $user = (new AdminUserModel)->wherePk($user_id)->find();
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
            Cookie::delete(self::COOKIE_LASTLOVE);
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
     * @return string|null
     */
    public function getToken() :?string
    {
        return $this->sessionToken;
    }

    /**
     * 验证会话
     * @return bool
     * @throws \db\exception\ModelException
     */
    public function verify()
    {
        if (null !== $this->verifyResult) {
            return $this->verifyResult;
        }
        try {
            if (!$this->sessionToken || !$this->sessionId) {
                throw new BusinessResultSuccess('钥匙丢失');
            }
            if (Session2::get(self::CONV_ADMIN_TOKEN) !== $this->sessionToken) {
                throw new BusinessResultSuccess('令牌错误');
            }
            $conv_info = Session2::get(self::CONV_ADMIN_INFO);
            if (!$conv_info || !is_array($conv_info)) {
                throw new BusinessResultSuccess('授权丢失');
            }
            $user_agent = request()->header('User-Agent');
            if ($this->sess_user_agent !== crc32($user_agent)) {
                throw new BusinessResultSuccess('授权无效');
            }
            if (time() > $this->sess_access_time) {
                throw new BusinessResultSuccess('状态超时');
            }
            $user = $this->getAdminUser();
            if ($this->sess_login_time !== $user->last_login_time ||
                self::generateUserFeature($user) !== $this->sess_user_feature
            ) {
                throw new BusinessResultSuccess('发生更变');
            }
            if (AdminUser::STATUS_NORMAL !== $user->status || $this->sess_user_status !== $user->status) {
                throw new BusinessResultSuccess("状态：{$user->status_desc}");
            }
            if ($user->role_id !== $this->sess_role_id
                || ($user->role_id && $user->role && $user->role->update_time !== $this->sess_role_time)
            ) {
                throw new BusinessResultSuccess('角色发生更变');
            }
            if ($user->role_id && $user->role && AdminRole::STATUS_NORMAL !== $user->role->status) {
                throw new BusinessResultSuccess("角色状态：{$user->role->status_desc}");
            }
            // 会话续期
            $this->flushExpired();
        } catch (BusinessResultSuccess $result) {
            $this->destroy();
            $this->errorMessage = $result->getMessage();
            return $this->verifyResult = false;
        }

        return $this->verifyResult = true;
    }

    /**
     * 会话续期
     * @author NHZEXG
     */
    public function flushExpired()
    {
        $this->sess_access_time = (time() + self::CONV_TIME_OUT);
        $this->saveConvInfo();
    }

    /**
     * 销毁会话/
     * @param bool $destroy_remember 销毁记住登陆
     * @throws \db\exception\ModelException
     */
    public function destroy(bool $destroy_remember = false)
    {
        if ($destroy_remember) {
            $user = $this->getAdminUser();
            if ($user instanceof AdminUser) {
                $user->remember = get_rand_str(16);
                $user->save();
            }
            Cookie::delete(self::COOKIE_LASTLOVE);
        }
        // 销毁 Session
        Session2::destroy();
        // 清除 Session Cookie
        Cookie::delete($this->convCookieName);
        // 清除 Token Cookie
        Cookie::delete(self::COOKIE_CONV_TOKEN);
    }
}
