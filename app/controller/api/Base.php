<?php

namespace app\controller\api;

use app\BaseController;

class Base extends BaseController
{
    /** @var array */
    private $allowInputFields;

    /**
     * 获取验证中间件传递的许可字段
     * @return array
     */
    protected function getAllowInputFields()
    {
        if ($this->allowInputFields === null) {
            $this->allowInputFields = $this->request->middleware('allow_input_fields', []);
        }
        return $this->allowInputFields;
    }

    /**
     * 获取过滤后的输入
     * @return array
     */
    protected function getFilterInput()
    {
        return $this->request->only($this->getAllowInputFields());
    }
}
