<?php
declare(strict_types=1);

namespace app\Service\WebConv\Concern;

use app\Exception\BusinessResult as BusinessResultSuccess;
use app\Model\AdminUser as AdminUserModel;
use app\Server\DeployInfo;
use Hashids\Hashids;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;

/**
 * Trait RememberToken
 * @package app\Service\WebConv\Concern
 */
trait RememberToken
{
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
                $value = $this->app->cookie->get(self::COOKIE_LASTLOVE, '');
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
            $this->app->cookie->delete(self::COOKIE_LASTLOVE);
            return null;
        }

        return $user;
    }
}
