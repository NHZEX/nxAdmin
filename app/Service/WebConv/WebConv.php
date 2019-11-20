<?php
declare(strict_types=1);

namespace app\Service\WebConv;

use app\Exception\BusinessResult as BusinessResultSuccess;
use app\Model\AdminRole;
use app\Model\AdminUser as AdminUserModel;
use app\Service\WebConv\Concern\ConvUserInfo;
use app\Service\WebConv\Concern\RememberToken;
use Serializable;
use think\App;
use think\Config;

/**
 * Class WebConv
 * @package app\Service\WebConv
 * @deprecated
 */
class WebConv implements Serializable
{
    use ConvUserInfo;
    use RememberToken;

    /**
     * @var App
     */
    private $app;

    /**
     * 会话超时时间
     * @var int
     */
    private $sessTimeOut = 7200;

    /**
     * 错误信息
     * @var string
     */
    private $errorMessage;

    /**
     * 验证结果
     * @var bool
     */
    private $verifyResult;

    /**
     * 会话用户
     * @var AdminUserModel
     */
    private $convUser;

    // 记住我令牌
    const COOKIE_LASTLOVE = 'lastlove';
    // 会话创建时间
    const CONV_CREATE_TIME = 'create_time';
    // 会话信息
    const CONV_ADMIN_INFO = 'conv_info';
    // 会话Token
    const CONV_ADMIN_TOKEN = 'conv_token';
    // 会话密钥
    const CONV_COMMON_KEY = 'common_key';
    // 最近一次访问时间
    const CONV_ACCESS_TIME = 'access_time';
    // 会话刷新超时
    const SESS_REFRESH_TIME_OUT = 0; // 每3小时刷新一次 SESSION_ID

    /**
     * String representation of object TODO 未测试是否正常
     * @link  https://php.net/manual/en/serializable.serialize.php
     * @return string the string representation of the object or null
     * @since 5.1.0
     */
    public function serialize()
    {
        return serialize([
            'sessionId' => $this->app->session->getId(),
            'sessionData' => $this->app->session->all(),
            'verifyResult' => $this->verifyResult,
        ]);
    }

    /**
     * Constructs the object
     * @link  https://php.net/manual/en/serializable.unserialize.php
     * @param string $serialized
     * @return void
     */
    public function unserialize($serialized)
    {
        $this->app = App::getInstance();
        $this->app->bind('webconv', $this);

        $this->sessTimeOut = $this->app->config->get('session.expire', 7200);

        $data = unserialize($serialized);
        $this->verifyResult = $data['verifyResult'];

        $this->app->session->setId($data['sessionId']);
        $this->app->session->setData($data['sessionData']);
        $this->app->request->withSession($this->app->session);
    }

    /**
     * WebConv constructor.
     * @param App    $app
     * @param Config $config
     */
    public function __construct(App $app, Config $config)
    {
        $this->app = $app;
        $this->sessTimeOut = $config->get('session.expire', 7200);
    }

    /**
     * 获取错误信息
     * @access public
     * @return string
     */
    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    /**
     * @param AdminUserModel $user
     * @param bool           $rememberme
     * @return self
     */
    public function createSession(AdminUserModel $user, bool $rememberme = false): self
    {
        $request = $this->app->request;
        $session = $this->app->session;
        $cookie = $this->app->cookie;

        // 获取特征串 (必然重复/只做辅助识别)
        $user_agent = $request->header('User-Agent');

        // 用户特征
        $user_feature = self::generateUserFeature($user);
        $user_role = $user->role_id ? $user->role : null;
        // 会话信息
        $conv_info = $this->generateConvInfo($user, $user_role, $user_feature, $user_agent);

        // 记住登录状态
        if ($rememberme) {
            $rememberme_out_time = 604800; // 7 day
            $token = $this->createRememberToken($user, $user_agent, $rememberme_out_time);
            $cookie->set(self::COOKIE_LASTLOVE, $token, [
                'expire' => $rememberme_out_time,
                'httponly' => true,
            ]);
        }

        // 设置
        $this->flushExpired();
        $session->set(self::CONV_CREATE_TIME, time() + self::SESS_REFRESH_TIME_OUT);
        $session->set(self::CONV_ADMIN_INFO, $conv_info);
        $session->set(self::CONV_COMMON_KEY, get_rand_str(16));

        return $this;
    }

    /**
     * 会话续期
     * @param int|null $out_time
     * @author NHZEXG
     */
    public function flushExpired(?int $out_time = null)
    {
        $this->app->session->set(self::CONV_ACCESS_TIME, time() + ($out_time ?? $this->sessTimeOut));
    }

    /**
     * @param bool $force
     * @return AdminUserModel
     * @deprecated
     */
    public function getAdminUser(bool $force = false)
    {
        return $this->getConvUser($force);
    }

    /**
     * @noinspection PhpDocMissingThrowsInspection
     * @param bool $force
     * @return AdminUserModel
     */
    public function getConvUser(bool $force = false)
    {
        if ($force || false === $this->convUser instanceof AdminUserModel) {
            /** @noinspection PhpUnhandledExceptionInspection */
            $this->convUser = (new AdminUserModel())->wherePk($this->getUserId())->find();
        }
        return $this->convUser;
    }

    /**
     * @return bool
     */
    public function isSuperAdmin(): bool
    {
        return $this->getUserGenre() === AdminUserModel::GENRE_SUPER_ADMIN;
    }

    /**
     * 获取会话结果
     * @return bool|null
     */
    public function lookVerify(): ?bool
    {
        return $this->verifyResult;
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
            if (empty($this->app->session->all())) {
                throw new BusinessResultSuccess('会话不存在');
            }
            if ($curr_time > $this->app->session->get(self::CONV_ACCESS_TIME)) {
                throw new BusinessResultSuccess('会话过期');
            }
            $user_agent = $this->app->request->header('User-Agent');
            if ($this->getBrowserUserAgent() !== crc32($user_agent)) {
                throw new BusinessResultSuccess('会话环境不一致');
            }
            $user = $this->getConvUser();
            if ($this->getLoginTime() !== $user->last_login_time ||
                self::generateUserFeature($user) !== $this->getUserFeature()
            ) {
                throw new BusinessResultSuccess('用户状态发生更变');
            }
            if (AdminUserModel::STATUS_NORMAL !== $user->status || $this->getUserStatus() !== $user->status) {
                throw new BusinessResultSuccess("用户状态：{$user->status_desc}");
            }
            if ($user->role_id !== $this->getRoleId()
                || ($user->role_id && $user->role && $user->role->update_time !== $this->getRoleUpdateTime())
            ) {
                throw new BusinessResultSuccess('角色状态发生更变');
            }
            if ($user->role_id && $user->role && AdminRole::STATUS_NORMAL !== $user->role->status) {
                throw new BusinessResultSuccess("角色状态：{$user->role->status_desc}");
            }
        } catch (BusinessResultSuccess $result) {
            // $this->destroy();
            $this->errorMessage = $result->getMessage();
            return $this->verifyResult = false;
        }

        // 会话续期
        $this->flushExpired();

        return $this->verifyResult = true;
    }

    /**
     * 销毁会话/
     * @param bool $destroy_remember 销毁记住登陆
     */
    public function destroy(bool $destroy_remember = false)
    {
        if ($destroy_remember) {
            $user = $this->getConvUser();
            if ($user instanceof AdminUserModel) {
                $user->remember = get_rand_str(16);
                $user->save();
            }
            $this->app->cookie->delete(self::COOKIE_LASTLOVE);
        }
        // 销毁 Session
        $this->app->session->destroy();
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
}
