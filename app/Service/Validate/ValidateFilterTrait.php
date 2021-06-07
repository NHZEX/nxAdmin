<?php

declare(strict_types=1);

namespace app\Service\Validate;

use think\Request;
use Zxin\Think\Validate\ValidateContext;

/**
 * Trait ValidateFilter
 * @package app\Traits
 * @property Request $request
 */
trait ValidateFilterTrait
{
    /** @var array */
    private $allowInputFields;

    /**
     * 获取验证中间件传递的许可字段
     * @return array
     */
    protected function getAllowInputFields(): array
    {
        $ctx = ValidateContext::get();
        if ($ctx === null) {
            return [];
        }
        if ($this->allowInputFields === null) {
            $this->allowInputFields = $ctx->getInputFields();
        }
        return $this->allowInputFields;
    }

    /**
     * 获取过滤后的输入
     * @return array
     */
    protected function getFilterInput(): array
    {
        return $this->request->only($this->getAllowInputFields());
    }
}