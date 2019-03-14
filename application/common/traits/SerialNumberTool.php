<?php
/**
 * Created by PhpStorm.
 * User: NHZEXG
 * Date: 2018/9/25
 * Time: 17:20
 */

namespace app\common\traits;

use app\server\RedisProxy;

trait SerialNumberTool
{
    // 36进制码表
    static $_SerialNumberTool_sample = '0123456789abcdefghijklmnopqrstuvwxyz';
    // 序列号最大位数
    static $_SerialNumberTool_max_length = 32;

    /**
     * [静态] 生成流水序列号
     * @author NHZEXG
     * @param int $digits 补位: 0-12
     * @param null|string $prefix 前缀
     * @return string
     * @throws \Exception
     */
    public static function getSerialNumber(int $digits = 13,  ?string $prefix = null)
    {
        // 调整补位
        $prefix && $digits = $digits - strlen($prefix);
        // 输入校验
        if($digits < 0 || $digits > 13) {
            throw new \Exception("补位位数超出有效范围 0-13, 当前 {$digits}");
        }
        if($prefix && $len = strlen($prefix)) {
            for($ii = 0; $ii < $len; $ii++){
                if (is_numeric($prefix[$ii])) {
                    throw new \Exception('前缀不能包含数字');
                }
            }
        }
        // 实例化
        $redis = RedisProxy::getInstance();
        // 获取时间
        $time = gettimeofday();
        // 获取日期
        $serial = gmdate('Ymd', $time['sec']);
        // 5位自增数 1 - 65535
        $inc = $redis->serialInc(gmdate('YmdHis', $time['sec']));
        // his 000001 - 235959
        $his = gmdate('His', $time['sec']);
        // 5位毫秒数 0 - 99999 补位
        $usec = str_pad(substr($time['usec'], 0, 5), 5, '0', STR_PAD_LEFT);
        // 5位自增数 1 - 65535 补位
        $sinc = str_pad(empty($inc) ? 0 : min($inc, 65535), 4, '0', STR_PAD_LEFT);
        // 在满载最大值下 '235959-99999-65535' 使用 36进制编码 刚好10位
        $serial .= str_pad(
            base_convert($his . $usec . $sinc, 10, 36),
            10,
            '0',
            STR_PAD_LEFT
        );
        // 写入前缀
        $prefix && $serial = $prefix . $serial;
        // 计数自校验码
        $crc1 = crc32($serial) % strlen(self::$_SerialNumberTool_sample);
        // 生成随机补位
        $digits && $serial .= get_rand_str($digits, self::$_SerialNumberTool_sample);
        // 组装校验
        $serial .= self::$_SerialNumberTool_sample[$crc1];
        return strtoupper($serial);
    }

    /**
     * 测试序列号自校验
     * @param string $sn
     * @author NHZEXG
     * @return bool
     */
    public static function verifySerialNumber (string $sn) {

        // 寻找第一个数字
        $len = strlen($sn);
        $number_pos = 0;
        for ($i = 0; $len > $i; $i++) {
            if($i > 12) break;
            $ai = ord($sn[$i]);
            if($ai >=48 && $ai <= 57) {
                $number_pos = $i;
                break;
            }
        }
        $prefix = substr($sn, 0, $number_pos);
        $sn = substr($sn, $number_pos);
        $sn = strtolower($sn);
        // 提取校验码
        $crc = substr($sn, -1, 1);
        // 获取计算值
        $sn = substr($sn, 0, 18);
        $prefix && $sn = $prefix . $sn;
        // 计算当前校验码
        $n_crc1 = crc32($sn) % strlen(self::$_SerialNumberTool_sample);
        // 校验结果
        return $crc === self::$_SerialNumberTool_sample[$n_crc1];
    }

}
