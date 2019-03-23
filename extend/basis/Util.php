<?php
/**
 * Created by PhpStorm.
 * User: NHZEXG
 * Date: 2019/3/6
 * Time: 15:10
 */
namespace basis;

class Util
{
    /**
     * 转换为下划线命名
     * @param string $input
     * @return string
     */
    public static function toSnakeCase(string $input): string
    {
        // 小写和大写紧挨一起的地方,加上分隔符,然后全部转小写
        return strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $input));
    }

    /**
     * 转换为大驼峰命名
     * @param string $input
     * @return string
     */
    public static function toUpperCamelCase(string $input): string
    {
        /**
         * step1.原字符串转换下划线命名
         * step3.转换每个单词的首字母到大写
         * step4.移除所有下划线
         */
        $separator = '_';
        $uncamelized_words = self::toSnakeCase($input);
        $uncamelized_words = ucwords($uncamelized_words, '_');
        $uncamelized_words = str_replace($separator, '', $uncamelized_words);
        return $uncamelized_words;
    }

    /**
     * 转换为小驼峰命名
     * @param string $input
     * @return string
     */
    public static function toLowerCamelCase(string $input): string
    {
        return lcfirst(self::toUpperCamelCase($input));
    }

    /**
     * 获取当前进程用户
     * @return string
     */
    public static function whoami(): string
    {
        return posix_getpwuid(posix_geteuid())['name'];
    }
}
