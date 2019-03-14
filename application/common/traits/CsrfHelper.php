<?php
/**
 * Created by PhpStorm.
 * User: NHZEXG
 * Date: 2019/1/8
 * Time: 16:47
 */

namespace app\common\traits;

use app\exception\InvalidAuthorization;
use app\server\Deploy;
use facade\Redis;
use facade\WebConv;
use Hashids\Hashids;
use MessagePack\MessagePack;
use struct\CsrfStruct;

trait CsrfHelper
{
    /**
     * @return CsrfStruct
     */
    protected function getRequestCsrfToken()
    {
        $csrf = \think\facade\Request::header(CSRF_TOKEN, '.');
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
        $hashids = new Hashids(WebConv::getSelf()->getToken(), 16);
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
        $hashids = new Hashids(WebConv::getSelf()->getToken(), 16);
        [$pkid, $lock_version] = $hashids->decode($csrf_token->token);
        return [$pkid, $lock_version];
    }

    /**
     * 生成令牌
     * @param string $mac
     * @param string $appid
     * @return string
     */
    public function generateApiToken(string $mac, string $appid) :?string
    {
        $timeOut = 7200;
        $version = dechex(API_TOKEN_V1);
        $data = [
            'iat' => time(),
            'exp' => time() + $timeOut,
            'mac' => $mac,
            'uuid' => hash('fnv1a32', $appid),
        ];

        $payload = base64url_encode(MessagePack::pack($data));
        $signature = base64url_encode(hash_hmac('SHA3-224', $payload, Deploy::getSecuritySalt(), true));

        $token = $version . '.' . $payload . '.' . $signature;
        $result = $this->registerApiToken($mac, $token, $timeOut);

        return $result ? $token : null;
    }

    /**
     * 解析令牌
     * @param string $token
     * @param string $appid
     * @return string
     * @throws InvalidAuthorization
     */
    public function parseApiToken(string $token, string $appid)
    {
        $result = explode('.', $token, 3);
        if (3 !== count($result)) {
            throw new InvalidAuthorization('格式无效', CODE_CONV_AUTHOR_INVALID);
        }
        [$version, $payload, $signature] = $result;

        if (dechex(API_TOKEN_V1) !== $version) {
            throw new InvalidAuthorization('无法识别的版本', CODE_CONV_AUTHOR_INVALID);
        }

        $currSignature = base64url_encode(hash_hmac('SHA3-224', $payload, Deploy::getSecuritySalt(), true));
        if ($signature !== $currSignature) {
            throw new InvalidAuthorization('签名无效', CODE_CONV_AUTHOR_INVALID);
        }

        $payload = base64url_decode($payload);
        if (empty($payload)) {
            throw new InvalidAuthorization('载荷无效', CODE_CONV_AUTHOR_INVALID);
        }

        $data = MessagePack::unpack($payload);
        if (empty($data)) {
            throw new InvalidAuthorization('载荷无效', CODE_CONV_AUTHOR_INVALID);
        }

        if (($data['exp'] ?? 0) < time()) {
            throw new InvalidAuthorization('过期失效', CODE_CONV_AUTHOR_INVALID);
        }

        if (($data['uuid'] ?? '') !== hash('fnv1a32', $appid)) {
            throw new InvalidAuthorization('UUID无效', CODE_CONV_AUTHOR_INVALID);
        }

        if (false === $this->verifyApiToken($data['mac'], $token)) {
            throw new InvalidAuthorization('没有注册', CODE_CONV_AUTHOR_INVALID);
        }

        return $data['mac'];
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
        $prefix = Deploy::getMixingPrefix();
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
        $prefix = Deploy::getMixingPrefix();
        $key = "{$prefix}_token:{$tokenKey}:{$token}";
        return Redis::getSelf()->del($key) > 0;
    }

    /**
     * 注册Api令牌
     * @param string $mac
     * @param string $token
     * @param int    $timeOut
     * @param int    $max
     * @return bool
     */
    protected function registerApiToken(string $mac, string $token, int $timeOut = 7200, int $max = 5) :bool
    {
        $prefix = Deploy::getMixingPrefix();
        $redis = Redis::getSelf();
        $key = $prefix . '_token:api:' . $token;
        $setKey = $prefix . '_token:apilist:' . str_replace(':', '', $mac);

        // 滚动删除
        $redis->lPush($setKey, $token);
        $overflow = $redis->lLen($setKey) - $max;
        $redis->multi();
        while (0 < $overflow--) {
            $redis->rPop($setKey);
        }
        // 执行事务1
        $result = $redis->exec() ?? [];

        $redis->multi();
        // 移除无效数据
        foreach ($result as $value) {
            $redis->del($prefix . '_token:api:' . $value);
        }
        // 设置令牌
        $redis->expire($setKey, $timeOut);
        $redis->set($key, 1, $timeOut);
        // 执行事务2
        $result = $redis->exec();

        return array_pop($result);
    }

    /**
     * 验证Api令牌
     * @param string $mac
     * @param string $token
     * @param int    $timeOut
     * @return bool
     */
    protected function verifyApiToken(string $mac, string $token, int $timeOut = 7200) :bool
    {
        $prefix = Deploy::getMixingPrefix();
        $redis = Redis::getSelf();
        $key = $prefix . '_token:api:' . $token;
        $setKey = $prefix . '_token:apilist:' . str_replace(':', '', $mac);

        $redis->multi();
        $redis->exists($key);
        $redis->expire($setKey, $timeOut);
        $redis->expire($key, $timeOut);
        $result = $redis->exec();

        return array_shift($result);
    }
}
