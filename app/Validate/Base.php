<?php

namespace app\Validate;

use think\Validate;
use function explode;
use function in_array;
use function strpos;

abstract class Base extends Validate
{
    /**
     * 判断是否为整数
     * @param $value
     * @return bool
     */
    protected function isPositiveInteger($value)
    {
        if (filter_var($value, FILTER_VALIDATE_INT) && $value > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 获取当前验证器生效的字段
     * @return array
     */
    public function getRuleKeys()
    {
        $rules = $this->rule;

        // 如果thinkphp同意调整场景优先级，质量将需要跟着调整
        if ($this->currentScene) {
            $this->getScene($this->currentScene);
        }

        foreach ($this->append as $key => $rule) {
            if (!isset($rules[$key])) {
                $rules[$key] = $rule;
            }
        }

        $result = [];

        foreach ($rules as $key => $rule) {
            if (strpos($key, '|')) {
                // 字段|描述 用于指定属性名称
                [$key] = explode('|', $key);
            }

            // 场景检测
            if (!empty($this->only) && !in_array($key, $this->only)) {
                continue;
            }

            $result[] = $key;
        }

        return $result;
    }
}
