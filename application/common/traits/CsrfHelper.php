<?php
/**
 * Created by PhpStorm.
 * User: NHZEXG
 * Date: 2019/1/8
 * Time: 16:47
 */

namespace app\common\traits;

use app\server\DeployInfo;
use facade\Redis;
use facade\WebConv;
use Hashids\Hashids;
use struct\CsrfStruct;
use think\facade\Request;

trait CsrfHelper
{
    /**
     * @return CsrfStruct
     */
    protected function getRequestCsrfToken()
    {
        $csrf = Request::header(CSRF_TOKEN, '.');
        $csrf_value = explode('.', $csrf, 2);
        isset($csrf_value[1]) || $csrf_value[] = '';
        return new CsrfStruct(['token' => $csrf_value[0], 'mode' => $csrf_value[1]]);
    }

    /**
     * 生成简单CSRF令牌
     * @param bool $enable 使能令牌
     * @return string
     */
    protected function generateCsrfTokenSimple(bool $enable = true)
    {
        $token = get_rand_str(16) . '.default';
        $enable && $this->addCsrfToken($token);
        return $token;
    }

    /**
     * 生成模型CSRF令牌
     * @param int  $pk_id
     * @param int  $lock_version
     * @param bool $enable 使能令牌
     * @return bool
     */
    protected function generateCsrfToken(int $pk_id, int $lock_version, bool $enable = true)
    {
        $hashids = new Hashids(WebConv::getSelf()->getSessionId(), 16);
        $result = $hashids->encode($pk_id, $lock_version, mt_rand());
        $result .= '.update';
        $enable && $this->addCsrfToken($result);
        return $result;
    }

    /**
     * @param CsrfStruct $csrf_token
     * @return array [$pkid, $lock_version]
     */
    protected function parseCsrfToken(CsrfStruct $csrf_token)
    {
        $hashids = new Hashids(WebConv::getSelf()->getSessionId(), 16);
        [$pkid, $lock_version] = $hashids->decode($csrf_token->token);
        return [$pkid, $lock_version];
    }

    /**
     * 添加csrf令牌
     * @param string $token
     * @return bool
     */
    protected function addCsrfToken(string $token)
    {
        $conv = WebConv::getSelf();
        return $this->addToken($token, 'csrf:' . $conv->getSessionId());
    }

    /**
     * 添加审核令牌
     * @param string $token
     * @return bool
     */
    protected function addReviewToken(string $token)
    {
        return $this->addToken($token, 'review');
    }

    private function addToken(string $token, string $tokenKey, int $timeOut = 3600)
    {
        $prefix = DeployInfo::getMixingPrefix();
        $key = "{$prefix}_token:{$tokenKey}:{$token}";
        return Redis::getSelf()->set($key, 1, $timeOut);
    }

    /**
     * 验证csrf令牌
     * @param string $token
     * @return bool
     */
    protected function verifyCsrfToken(string $token)
    {
        $conv = WebConv::getSelf();
        return $this->verifyToken($token, 'csrf:' . $conv->getSessionId());
    }

    /**
     * 验证审核令牌
     * @param string $token
     * @return bool
     */
    protected function verifyReviewToken(string $token)
    {
        return $this->verifyToken($token, 'review');
    }

    /**
     * @param string $token 令牌
     * @param string $tokenKey 令牌前缀
     * @return bool
     */
    private function verifyToken(string $token, string $tokenKey)
    {
        $prefix = DeployInfo::getMixingPrefix();
        $key = "{$prefix}_token:{$tokenKey}:{$token}";
        return Redis::getSelf()->del($key) > 0;
    }
}
