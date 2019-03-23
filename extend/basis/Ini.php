<?php
/**
 * Created by PhpStorm.
 * User: NHZEXG
 * Date: 2019/1/28
 * Time: 10:42
 */

namespace basis;

use Matomo\Ini\IniReader;
use Matomo\Ini\IniWriter;

class Ini
{
    const HEADER_DATE = 'date';

    /**
     * 读取INI
     * @param string $file_path
     * @return array
     * @throws \Matomo\Ini\IniReadingException
     */
    public static function readerFile(string $file_path)
    {
        $reader = new IniReader();
        return $reader->readFile($file_path);
    }

    /**
     * 写入INI
     * @param string $file_path
     * @param array $contents
     * @param string $header
     * @throws \Matomo\Ini\IniWritingException
     */
    public static function writerFile(string $file_path, array $contents, string $header = '')
    {
        $writer = new IniWriter();
        if ($header === self::HEADER_DATE) {
            $header = '; Date:' . date('c') . "\n\n";
        }
        $writer->writeToFile($file_path, $contents, $header);
    }

    /**
     * 写入INI
     * @param array $contents
     * @param string $header
     * @return string
     * @throws \Matomo\Ini\IniWritingException
     */
    public static function writer(array $contents, string $header = '')
    {
        $writer = new IniWriter();
        if ($header === self::HEADER_DATE) {
            $header = '; Date:' . date('c') . "\n\n";
        }
        return $writer->writeToString($contents, $header);
    }
}
