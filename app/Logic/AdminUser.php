<?php

namespace app\Logic;

use app\Exception\BusinessResult;
use app\Model\AdminUser as AdminUserModel;
use RuntimeException;
use think\db\exception\DbException;
use think\facade\Request;
use Zxin\Think\Auth\AuthGuard;
use function app;
use function filter_var;
use function time;

class AdminUser extends Base
{
    public const LOGIN_TYPE_NAME = 'username';
    public const LOGIN_TYPE_EMAIL = 'email';

    /**
     * @var AuthGuard
     */
    protected $auth;

    public function __construct(AuthGuard $authGuard)
    {
        $this->auth = $authGuard;
    }

    /**
     * @return AuthGuard
     */
    public function getAuth(): AuthGuard
    {
        return $this->auth;
    }

    /**
     * 用户登陆 邮箱或用户名
     * @param string $username
     * @param string $password
     * @param bool   $rememberme
     * @return bool
     */
    public function loginNameWaitEmail(string $username, string $password, bool $rememberme = false)
    {
        if (filter_var($username, FILTER_VALIDATE_EMAIL)) {
            return $this->login(self::LOGIN_TYPE_EMAIL, $username, $password, $rememberme);
        } else {
            return $this->login(self::LOGIN_TYPE_NAME, $username, $password, $rememberme);
        }
    }

    /**
     * 用户登陆 自定义
     * @param string $type
     * @param string $username
     * @param string $password
     * @param bool $rememberme
     * @return bool
     */
    public function login(string $type, string $username, string $password, bool $rememberme = false)
    {
        try {
            // todo 防止账号登录失败导致会话残留
            app()->session->clear();
            $user = match ($type) {
                self::LOGIN_TYPE_NAME => (new AdminUserModel())->where('username', $username)->find(),
                self::LOGIN_TYPE_EMAIL => (new AdminUserModel())->where('email', $username)->find(),
                default => throw new RuntimeException("无法处理的类型：{$type}"),
            };
            if (false === $user instanceof AdminUserModel) {
                throw new BusinessResult('账号或密码错误');
            }
            if (false === $user->verifyPassword($password)) {
                throw new BusinessResult('账号或密码错误');
            }
            if (AdminUserModel::STATUS_NORMAL !== $user->status) {
                throw new BusinessResult("账号状态：{$user->status_desc}");
            }

            $user->last_login_time = time();
            $user->last_login_ip = Request::ip();
            $user->withoutWriteAccessLimit();
            if ($user->save()) {
                // 创建会话
                $this->auth->login($user, $rememberme);
            } else {
                throw new BusinessResult('登录信息更新失败，请重试');
            }
        } catch (BusinessResult $businessResult) {
            $this->errorMessage = $businessResult->getMessage();
            return false;
        } catch (DbException $e) {
            throw new RuntimeException("数据库访问异常：{$e->getMessage()}", 0, $e);
        }
        return true;
    }
}
