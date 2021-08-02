<?php

namespace app\Service\Captcha;

use app\Exception\BusinessResult;
use RuntimeException;
use Zxin\Captcha\CaptchaValidatorAbstract;
use Zxin\Think\Redis\RedisManager;
use function base64_decode;
use function base64_encode;
use function crc32;
use function hash;
use function request;
use function serialize;
use function time;
use function unserialize;
use function Zxin\Crypto\decrypt_data;
use function Zxin\Crypto\encrypt_data;

class CaptchaValidatorToken extends CaptchaValidatorAbstract
{
    public function generateToken(): string
    {
        $require = request();
        $ua = $require->header('User-Agent');
        $palyload = [
            'hc' => $this->captcha->getCode(),
            'ip' => $require->ip(),
            'ttl' => time() + $this->ttl,
            'ua' => crc32($ua),
        ];

        $ciphertext = encrypt_data(serialize($palyload), $this->secureKey, 'aes-128-gcm', 'captcha');

        return base64_encode($ciphertext);
    }

    public function verifyToken(string $token, string $code): bool
    {
        $this->message = '验证码无效.';
        $ciphertext = base64_decode($token, true);
        if (empty($ciphertext)) {
            return false;
        }
        try {
            $plaintext = decrypt_data($ciphertext, $this->secureKey, 'aes-128-gcm', 'captcha');
        } catch (RuntimeException $exception) {
            return false;
        }
        $palyload = unserialize($plaintext, [
            'allowed_classes' => false,
        ]);
        if (empty($palyload)) {
            return false;
        }
        $require = request();
        $redis = RedisManager::connection();
        $key = "captcha:blacklist:" . hash('sha1', $token);
        try {
            if (!isset($palyload['ttl']) || time() > $palyload['ttl']) {
                throw new BusinessResult('验证码失效.');
            }
            if (!isset($palyload['ip']) || $require->ip() !== $palyload['ip']) {
                throw new BusinessResult('验证码无效.');
            }
            $ua = $require->header('User-Agent');
            if (!isset($palyload['ua']) || crc32($ua) !== $palyload['ua']) {
                throw new BusinessResult('验证码无效.');
            }
            if (!isset($palyload['hc']) || !$this->captcha->check($code, $palyload['hc'])) {
                throw new BusinessResult('验证码错误.');
            }
            if ($redis->exists($key)) {
                throw new BusinessResult('验证码无效.');
            }
        } catch (BusinessResult $result) {
            $this->message = $result->getMessage();
            return false;
        } finally {
            $redis->setex($key, $this->ttl, time() . "|{$require->ip()}");
        }
        return true;
    }
}
