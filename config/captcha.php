<?php
/**
 * Created by PhpStorm.
 * User: NHZEXG
 * Date: 2018/12/18
 * Time: 10:03
 */
return [
    // 验证码加密密钥
    'seKey'    => \app\server\DeployInfo::getSecuritySalt() ?? 'BW4IvwzuiXc7ijH85yfNPbIPlrjbU0hT',
    // 验证码过期时间（s）
    'expire'   => 120,
    // 验证码图片高度
    'imageH'   => 38,
    // 验证码图片宽度
    'imageW'   => 130,
    // 验证码字体大小(px)
    'fontSize' => 18,
    // 验证码位数
    'length'   => 4,
    // 验证码字体
    'fontttf'  => '4.ttf',
    // 是否画混淆曲线
    'useCurve' => true,
    // 是否添加杂点
    'useNoise' => true,
];
