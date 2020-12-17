<?php

namespace app\Service\Validate;

use think\Request;

/**
 * Interface AskSceneInterface
 * @package app\Service\Validate
 * @deprecated
 */
interface AskSceneInterface
{
    /**
     * 询问当前应当使用何种场景
     * @param Request $request
     * @return string|null
     */
    public static function askScene(Request $request): ?string;
}
