<?php

use app\Service\Captcha\CaptchaValidatorToken;
use Zxin\Captcha\Captcha;

const CAPTCHA_THROTTLE_RATE = '10/m';

return [
    // 访问限制
    'throttle_rate'  => CAPTCHA_THROTTLE_RATE,
    // 验证码图片高度
    'imageH'         => 38,
    // 验证码图片宽度
    'imageW'         => 130,
    // 验证码字体大小(px)
    'fontSize'       => 18,
    // 验证码位数
    'length'         => 4,
    // 验证码字体
    'fontttfs'       => [],
    // 是否画混淆曲线
    'useCurve'       => true,
    // 是否添加杂点
    'useNoise'       => true,
    // 使用背景图片
    'useImgBg'       => false,
    // 输出类型
    'outputType'     => Captcha::OUTPUT_WEBP,
    // 验证码密钥
    'secureKey'      => env('DEPLOY_SECURITY_SALT') ?? 'null',
    // 验证码过期时间（s）
    'expire'         => 120,
    // 验证驱动
    'validatorClass' => CaptchaValidatorToken::class, // CaptchaValidatorToken::class
];
