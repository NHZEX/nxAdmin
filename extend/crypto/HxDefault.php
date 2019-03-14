<?php
/**
 * Created by PhpStorm.
 * User: NHZEXG
 * Date: 2018/5/14
 * Time: 19:25
 * Ver: 1.0.0
 */

namespace crypto;

class HxDefault
{
    const JSON_CODING_PARAMETER = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;

    public static function hxAesEncMac101(){

    }

    public static function hxAesDecMac101(){

    }

    /**
     * HX021-AES
     * Aes 加密数据
     * @param string $in_data 待加密数据
     * @param string $enc_pwd 加密秘钥
     * @param bool $padding 是否自动填充到标准长度
     * @param string $method 加密算法名称
     * @return string         已加密数据
     * @throws \LengthException
     * @author NHZEXG
     */
    public static function hxAesEnc101(
        string $in_data,
        string $enc_pwd,
        bool $padding = false,
        string $method = 'AES-128-CFB'
    ): string
    {
        static $_methods = [
            'AES-128' => 16,
            'AES-192' => 24,
            'AES-156' => 32,
        ];

        $method = strtoupper($method);
        $bytes = $_methods[substr($method, 0, 7)] ?? 0;
        if (0 === $bytes) {
            throw new \LengthException("Unknown cipher algorithm");
        }
        if (strlen($enc_pwd) !== $bytes) {
            if ($padding) {
                $enc_pwd = substr(hash('md5', $enc_pwd, true), -1 * $bytes);
            } else {
                throw new \LengthException("cipher expects an password of precisely {$bytes} bytes");
            }
        }

        $iv_len = openssl_cipher_iv_length($method);
        $iv = openssl_random_pseudo_bytes($iv_len);
        $out_data = openssl_encrypt(
            $in_data,
            $method,
            $enc_pwd,
            OPENSSL_RAW_DATA,
            $iv
        );

        $sign = openssl_digest($out_data . $iv, 'md5', true);

        return base64_encode($out_data . $iv . $sign);
    }

    /**
     * HX021-AES
     * Aes 解密数据
     * @param string $in_data 待解密数据
     * @param string $dec_pwd 解密秘钥
     * @param bool $padding 是否自动填充到标准长度
     * @param string $method 加密算法名称
     * @return string         已解密数据
     * @author NHZEXG
     */
    public static function hxAesDec101(
        string $in_data,
        string $dec_pwd,
        bool $padding = false,
        string $method = 'AES-128-CFB'
    ): string
    {
        static $_methods = [
            'AES-128' => 16,
            'AES-192' => 24,
            'AES-156' => 32,
        ];

        $method = strtoupper($method);
        $bytes = $_methods[substr($method, 0, 7)] ?? 0;
        if (0 === $bytes) {
            throw new \LengthException("Unknown cipher algorithm");
        }

        if (strlen($dec_pwd) !== $bytes) {
            if ($padding) {
                $dec_pwd = substr(hash('md5', $dec_pwd, true), -1 * $bytes);
            } else {
                throw new \LengthException("cipher expects an password of precisely {$bytes} bytes");
            }
        }

        $in_data = base64_decode($in_data);

        $data_sign = substr($in_data, -16);
        $in_data = substr($in_data, 0, -16);

        if ($data_sign !== openssl_digest($in_data, 'md5', true)) {
            throw new \UnexpectedValueException("dec data hash fail");
        }

        $iv_len = openssl_cipher_iv_length($method);
        list($in_data, $iv) = [substr($in_data, 0, -1 * $iv_len), substr($in_data, -1 * $iv_len)];

        $out_date = openssl_decrypt(
            $in_data,
            'AES-128-CFB',
            $dec_pwd,
            OPENSSL_RAW_DATA,
            $iv
        );
        return $out_date;
    }

    /**
     * HX021-RSA
     * RSA 公钥加密数据
     * @param string $in_data 待加密数据
     * @param string $pub RSA公钥
     * @return array          已加密数据
     * @author NHZEXG
     * @throws \Exception
     */
    public static function hxRsaPubEnc101(string $in_data, string $pub): array
    {
        $pub_id = openssl_pkey_get_public($pub);

        if (false === $pub_id) {
            throw new \Exception('rsa public key load error: ' . openssl_error_string());
        }
        if (false === $details = openssl_pkey_get_details($pub_id)) {
            throw new \Exception('call openssl_pkey_get_details fails, ' . openssl_error_string());
        }
        if (2048 > $details['bits']) {
            throw new \Exception('rsa bits less than 2048');
        }

        $method = 'AES-128-CFB';
        $password = openssl_random_pseudo_bytes(32);

        if (false === openssl_public_encrypt(
                $password,
                $enc_pwd,
                $pub,
                OPENSSL_PKCS1_PADDING
            )
        ) {
            throw new \Exception(openssl_error_string());
        }

        $out_data = self::hxAesEnc101($in_data, substr($password, 0, 16), false, $method);

        return [$out_data, base64_encode($enc_pwd)];
    }

    /**
     * HX021-RSA
     * RSA 私钥解密数据
     * @param string $in_data 待加密数据
     * @param string $enc_pwd 加密的秘钥
     * @param string $pri RSA私钥
     * @param array $out_pwd 解码的秘钥
     * @return string          已解密数据
     * @throws \Exception
     * @author NHZEXG
     */
    public static function hxRsaPriDnc101(
        string $in_data,
        string $enc_pwd,
        string $pri,
        &$out_pwd = null
    ): string
    {
        $enc_pwd = base64_decode($enc_pwd);
        $pri_id = openssl_pkey_get_private($pri);

        if (false === $pri_id) {
            throw new \Exception('rsa private key load error: ' . openssl_error_string());
        }
        if (false === $details = openssl_pkey_get_details($pri_id)) {
            throw new \Exception('call openssl_pkey_get_details fails, ' . openssl_error_string());
        }
        if (2048 > $details['bits']) {
            throw new \Exception('rsa bits less than 2048');
        }
        if (2048 / 8 !== strlen($enc_pwd)) {
            throw new \Exception('rsa enc data len error');
        }

        if (false === openssl_private_decrypt(
                $enc_pwd,
                $dnc_pwd,
                $pri,
                OPENSSL_PKCS1_PADDING
            )
        ) {
            throw new \Exception(openssl_error_string());
        }
        $method = 'AES-128-CFB';
        $out_pwd = [substr($dnc_pwd, 0, 16), substr($dnc_pwd, -16)];
        return self::hxAesDec101($in_data, $out_pwd[0], false, $method);
    }

    /**
     * HX021-SIGN-MAC
     * Http 请求头 HMACSHA256签名生成
     * @param string $password 签名密码
     * @param string $appsn 应用SN
     * @param string $method 请求头
     * @param string $domain 资源域名
     * @param string $uri 资源地址
     * @param string $nonce 请求随机值
     * @param int $timestamp 请求时间
     * @author NHZEXG
     * @return string
     */
    public static function signHeadHmacSha256(
        string $password,
        string $appsn = 'A-A-A-A-A',
        string $method = 'GET',
        string $domain = 'abc.com',
        $uri = '/hello.html',
        &$nonce = 'ABCDEFGH',
        &$timestamp = 0
    ): string
    {

        $timestamp = time();
        $nonce = base_convert($timestamp, 10, 36) . self::getRandStr(8);
        $data = "{$appsn}:{$method}:{$domain}:{$uri}:{$nonce}:{$timestamp}";
        $sign = hash_hmac('sha256', $data, $password);
        return $sign;
    }

    /**
     * HX021-SIGN-MAC
     * Http 请求头 HMACSHA256签名验证
     * @param string $password 签名密码
     * @param string $sign 验证签名串
     * @param string $appsn 应用SN
     * @param string $method 请求头
     * @param string $domain 资源域名
     * @param string $uri 资源地址
     * @param string $nonce 请求随机值
     * @param int $timestamp 请求时间
     * @return bool
     * @author NHZEXG
     */
    public static function verifySignHeadHmacSha256(
        string $password,
        string $sign = '',
        string $appsn = 'A-A-A-A-A',
        string $method = 'GET',
        string $domain = 'abc.com',
        $uri = '/hello.html',
        $nonce = 'ABCDEFGH',
        $timestamp = 0
    ): bool
    {
        $data = "{$appsn}:{$method}:{$domain}:{$uri}:{$nonce}:{$timestamp}";
        return (bool)($sign === hash_hmac('sha256', $data, $password));
    }

    /**
     * HX021-SIGN-RSA
     * Http 请求头 RSA私钥签名生成
     * @param string $pri 签名私钥
     * @param string $appsn 应用SN
     * @param string $method 请求头
     * @param string $domain 资源域名
     * @param string $uri 资源地址
     * @param string $nonce 请求随机值
     * @param int $timestamp 请求时间
     * @author NHZEXG
     * @return string
     * @throws \Exception
     */
    public static function signHeadRsaWhitSha256(
        string $pri,
        string $appsn = 'A-A-A-A-A',
        string $method = 'GET',
        string $domain = 'abc.com',
        $uri = '/hello.html',
        &$nonce = 'ABCDEFGH',
        &$timestamp = 0
    ): string
    {
        $pri_id = openssl_pkey_get_private($pri);

        if (false === $pri_id) {
            throw new \Exception('rsa private key load error: ' . openssl_error_string());
        }
        if (false === $details = openssl_pkey_get_details($pri_id)) {
            throw new \Exception('call openssl_pkey_get_details fails, ' . openssl_error_string());
        }
        if (2048 > $details['bits']) {
            throw new \Exception('rsa bits less than 2048');
        }

        $timestamp = time();
        $nonce = base_convert($timestamp, 10, 36) . self::getRandStr(8);
        $data = "{$appsn}:{$method}:{$domain}:{$uri}:{$nonce}:{$timestamp}";

        if (false === openssl_sign($data, $sign, $pri_id, 'sha256')) {
            throw new \Exception(openssl_error_string());
        }
        return base64_encode($sign);
    }

    /**
     * HX021-SIGN-MAC
     * Http 请求头 HMACSHA256签名验证
     * @param string $pub 验证公钥
     * @param string $sign 验证签名串
     * @param string $appsn 应用SN
     * @param string $method 请求头
     * @param string $domain 资源域名
     * @param string $uri 资源地址
     * @param string $nonce 请求随机值
     * @param int $timestamp 请求时间
     * @return bool
     * @throws \Exception
     * @author NHZEXG
     */
    public static function verifySignHeadRsaWhitSha256(
        string $pub,
        string $sign = '',
        string $appsn = 'A-A-A-A-A',
        string $method = 'GET',
        string $domain = 'abc.com',
        $uri = '/hello.html',
        $nonce = 'ABCDEFGH',
        $timestamp = 0
    ): bool
    {
        $pub_id = openssl_pkey_get_public($pub);
        $sign = base64_decode($sign);

        if (false === $pub_id) {
            throw new \Exception('rsa private key load error: ' . openssl_error_string());
        }
        if (false === $details = openssl_pkey_get_details($pub_id)) {
            throw new \Exception('call openssl_pkey_get_details fails, ' . openssl_error_string());
        }
        if (2048 > $details['bits']) {
            throw new \Exception('rsa bits less than 2048');
        }
        $data = "{$appsn}:{$method}:{$domain}:{$uri}:{$nonce}:{$timestamp}";
        if (false === $bool = openssl_verify($data, $sign, $pub_id, 'sha256')) {
            throw new \Exception(openssl_error_string());
        }

        return $bool;
    }

    /**
     * Http 数据体 HmacSha256签名
     * @param array $data 数据体
     * @param string $password 签名秘钥
     * @author NHZEXG
     * @return string
     */
    public static function signDataHmacSha256(
        array $data,
        string $password
    ): string
    {

        ksort($data);
        $data = http_build_query($data, SORT_STRING);
        $sign = hash_hmac('sha256', $data, $password, false);

        return $sign;
    }

    /**
     * Http 数据体 HmacSha256签名验证
     * @param array $data 数据体
     * @param string $datasign 数据体签名
     * @param string $password 签名秘钥
     * @author NHZEXG
     * @return bool
     */
    public static function verifySignDataHmacSha256(
        array $data,
        string $datasign,
        string $password
    )
    {
        ksort($data);
        $data = http_build_query($data, SORT_STRING);
        $sign = hash_hmac('sha256', $data, $password, false);

        return (bool)($sign === $datasign);
    }

    /**
     * 请求签名计算
     * @param string $appid
     * @param string $method
     * @param string $path
     * @param int    $timestamp
     * @param string $nonce
     * @param string $userAgent
     * @param array  $data 数据体
     * @return string
     * @author NHZEXG
     */
    public static function signData2(
        string $appid,
        string $method, string $path,
        int $timestamp, string $nonce, string $userAgent,
        ?array $data
    ): string
    {
        $param = [
            'method' => $method,
            'path' => $path[0] === '/' ? $path : '/'.$path,
            'timestamp' => $timestamp,
            'nonce' => $nonce,
            'userAgent' => $userAgent,
            'data' => $data,
        ];

        $param = self::ksortNested($param);
        // 与JSON.stringify会存在行为上的少许不一致，大多数情况都是一致正确的
        // - 测试发现大数编码是javascript对丢失一部分值
        $param = json_encode($param, self::JSON_CODING_PARAMETER);
        $sign = 'a' . hash_hmac('sha1', $param, $appid, false);
        return $sign;
    }

    /**
     * 请求签名计算
     * User: Johnson
     * @param array  $data
     * @param string $timestamp
     * @param int    $nonce
     * @return string
     */
    public static function signData3(
        array $data, string $timestamp, int $nonce
    ): string
    {
        $data = self::ksortNested($data);

        $param = [
            'data' => $data,
            'timestamp' => $timestamp,
            'nonce' => $nonce,
        ];

        $param = self::ksortNested($param);
        // 与JSON.stringify会存在行为上的少许不一致，大多数情况都是一致正确的
        // - 测试发现大数编码是javascript对丢失一部分值
        $param = json_encode($param, self::JSON_CODING_PARAMETER);
        $sign = 'a' . hash('sha1', $param);
        return $sign;
    }

    /**
     * Http 数据体 HmacSha256签名 自定义算法
     * @param array $data 数据体
     * @param string $password 签名秘钥
     * @author NHZEXG
     * @return string
     */
    public static function sign2DataHmacSha256(
        array $data,
        string $password
    ): string
    {

        $data = self::arrayDecline($data);
        $sign = hash_hmac('sha256', $data, $password, false);

        return $sign;
    }

    /**
     * Http 数据体 HmacSha256签名验证 自定义算法
     * @param array $data 数据体
     * @param string $datasign 数据体签名
     * @param string $password 签名秘钥
     * @author NHZEXG
     * @return bool
     */
    public static function verifySign2DataHmacSha256(
        array $data,
        string $datasign,
        string $password
    )
    {
        $data = self::arrayDecline($data);
        $sign = hash_hmac('sha256', $data, $password, false);

        return (bool)($sign === $datasign);
    }

    /**
     * 数组转字符串 自定义算法
     * @param array $arr
     * @author NHZEXG
     * @return string
     */
    public static function arrayDecline(array $arr): string
    {
        $arrstr = [];
        $call = function ($value, $key, $op) use (&$arrstr) {
            list($call, $leve) = $op;
            if (is_array($value) || is_object($value)) {
                array_walk($value, $call, [$call, "$leve:$key"]);
            } else {
                $arrstr[] = "$leve:$key:$value";
            }
        };
        array_walk($arr, $call, [$call, 'r']);
        ksort($arrstr, SORT_STRING);
        return join('&', $arrstr);
    }

    /**
     * 获取随机字符串
     * @param int $length
     * @return string
     */
    public static function getRandStr(int $length = 8)
    {
        // 密码字符集
        static $chars = [
            'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u',
            'v', 'w', 'x', 'y', 'z', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O',
            'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9',
        ];
        //!@#$%^&*()-_ []{}<>~`+=,.;:/?|
        $text = '';
        $chars_max_index = count($chars) - 1;
        for ($i = 0; $i < $length; $i++) {
            $text .= $chars[mt_rand(0, $chars_max_index)];
        }
        return $text;
    }

    /**
     * 支持嵌套数组排序
     * @param $a
     * @return array
     */
    public static function ksortNested($a)
    {
        if (is_array($a)) {
            ksort($a);
            foreach ($a as $k => $v) {
                $a[$k] = self::ksortNested($v);
            }
        }
        return $a;
    }
}