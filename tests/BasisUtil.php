<?php
/**
 * Created by PhpStorm.
 * User: NHZEXG
 * Date: 2019/3/6
 * Time: 15:21
 */

namespace tests;

use basis\Util;

class BasisUtil extends \PHPUnit\Framework\TestCase
{
    public function testToLowerCamelCase()
    {
        $this->assertEquals('qweAsdZxc', Util::toLowerCamelCase('qwe_asd_zxc'));
        $this->assertEquals('qweAsdZxc', Util::toLowerCamelCase('qwe_Asd_Zxc'));
        $this->assertEquals('qweAsdZxc', Util::toLowerCamelCase('qweAsd_Zxc'));
    }

    public function testToUpperCamelCase()
    {
        $this->assertEquals('QweAsdZxc', Util::toUpperCamelCase('qwe_asd_zxc'));
        $this->assertEquals('QweAsdZxc', Util::toUpperCamelCase('qwe_Asd_Zxc'));
        $this->assertEquals('QweAsdZxc', Util::toUpperCamelCase('qweAsd_Zxc'));
    }

    public function testToSnakeCase()
    {
        $this->assertEquals('qwe_asd_zxc', Util::toSnakeCase('qweAsdZxc'));
        $this->assertEquals('qwe_asd_zxc', Util::toSnakeCase('QweAsdZxc'));
        $this->assertEquals('qwe_asd_zxc', Util::toSnakeCase('qwe_AsdZxc'));
    }
}
