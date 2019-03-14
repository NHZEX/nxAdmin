<?php
/**
 * Created by PhpStorm.
 * User: NHZEXG
 * Date: 2018/10/20
 * Time: 17:20
 */

namespace basis;

/**
 * Class IP
 * @package util
 *
 */
class IP
{
    /**
     * 获取客户端IP地址
     * @param boolean $adv 是否进行高级模式获取（有可能被伪装）
     * @return string|int
     */
    public static function getIp(bool $adv = false)
    {
        if ($adv) {
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $ip_array = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                if(is_array($ip_array)) {
                    $ip_array = array_map('trim', $ip_array);
                    $ip_array = array_filter($ip_array, function ($ip) {
                        return filter_var(
                            $ip, FILTER_VALIDATE_IP
                        );
                    });
                    $get_ip = array_shift($ip_array);
                }
            } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
                $get_ip = $_SERVER['HTTP_CLIENT_IP'];
            } elseif (isset($_SERVER['REMOTE_ADDR'])) {
                $get_ip = $_SERVER['REMOTE_ADDR'];
            }
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $get_ip = $_SERVER['REMOTE_ADDR'];
        }
        if(isset($get_ip)){
            $is_ipv4 = filter_var($get_ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
            $is_ipv6 = filter_var($get_ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);

            if (!$is_ipv4 && !$is_ipv6) {
                return '0.0.0.0';
            }
            return $get_ip;
        } else {
            return '0.0.0.0';
        }
    }
}
