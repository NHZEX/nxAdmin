<?php
declare(strict_types=1);

namespace app\Service\WebConv\Concern;

use app\Model\AdminRole;
use app\Model\AdminUser;
use think\App;

/**
 * Trait ConvUserInfo
 * @package app\Service\WebConv\Concern
 * @property App $app
 */
trait ConvUserInfo
{
    protected function generateConvInfo(AdminUser $user, ?AdminRole $role, string $user_feature, string $user_agent)
    {
        return [
            'user_id' => $user->id,
            'user_genre' => $user->genre,
            'user_status' => $user->status,
            'role_id' => $user->role_id,
            'role_update_time' => $role ? $role->update_time : 0,
            'login_time' => $user->last_login_time,
            'user_feature' => $user_feature,
            'browser_user_agent' => crc32($user_agent),
        ];
    }

    /**
     * 获取用户Id
     * @return int
     */
    public function getUserId()
    {
        return $this->app->request->session('conv_info.user_id');
    }

    /**
     * 获取用户类型
     * @return int
     */
    public function getUserGenre()
    {
        return $this->app->request->session('conv_info.user_genre');
    }

    /**
     * 获取用户特征
     * @return int
     */
    protected function getUserFeature()
    {
        return $this->app->request->session('conv_info.user_feature');
    }

    /**
     * 获取用户类型
     * @return int
     */
    protected function getUserStatus()
    {
        return $this->app->request->session('conv_info.user_status');
    }

    /**
     * 获取角色Id
     * @return int
     */
    public function getRoleId()
    {
        return $this->app->request->session('conv_info.role_id');
    }

    /**
     * 获取角色Id
     * @return int
     */
    protected function getRoleUpdateTime()
    {
        return $this->app->request->session('conv_info.role_update_time');
    }

    /**
     * 获取浏览器用户代理字符串
     * @return int
     */
    protected function getBrowserUserAgent()
    {
        return $this->app->request->session('conv_info.browser_user_agent');
    }

    /**
     * 获取会话登录时间
     * @return mixed
     */
    public function getLoginTime()
    {
        return $this->app->request->session('conv_info.login_time');
    }
}
