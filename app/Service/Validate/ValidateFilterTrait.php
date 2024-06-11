<?php

declare(strict_types=1);

namespace app\Service\Validate;

use think\Request;
use Zxin\Think\Validate\ValidateContext;

/**
 * Trait ValidateFilter.
 *
 * @property Request $request
 */
trait ValidateFilterTrait
{
    /** @var array */
    private $allowInputFields;

    /**
     * 获取验证中间件传递的许可字段.
     */
    protected function getAllowInputFields(): array
    {
        $ctx = ValidateContext::get();
        if (null === $ctx) {
            return [];
        }
        if (null === $this->allowInputFields) {
            $this->allowInputFields = $ctx->getInputFields();
        }

        return $this->allowInputFields;
    }

    /**
     * 获取过滤后的输入.
     */
    protected function getFilterInput(): array
    {
        return $this->request->only($this->getAllowInputFields());
    }
}
