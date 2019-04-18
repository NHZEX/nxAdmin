<?php
/**
 * Created by PhpStorm.
 * User: Auoor
 * Date: 2019/3/23
 * Time: 23:29
 */

namespace app\Validate;

use think\Request;

interface VailAsk
{
    /**
     * 询问当前应当使用何种场景
     * @param Request $request
     * @return string|null
     */
    public static function askScene(Request $request): ?string;
}
