<?php
/**
 * Created by PhpStorm.
 * User: NHZEXG
 * Date: 2018/12/21
 * Time: 10:18
 */

namespace app\controller;

use app\BaseController;
use app\Traits\CsrfHelper;
use app\Traits\ShowReturn;
use think\App;
use think\View;

abstract class AdminBase extends BaseController
{
    use ShowReturn;
    use CsrfHelper;

    /**
     * @var View
     */
    protected $view;

    public function __construct(App $app, View $view)
    {
        parent::__construct($app);

        $this->view = $view;
    }

    /**
     * 数据表字段名 - 页面属性，映射
     * @param array $mapping
     * @return array
     */
    public function buildParam(array $mapping)
    {
        $data = [];
        $input = $this->request->param();
        foreach ($mapping as $name => $alias) {
            if (is_int($name)) {
                $name = $alias;
            }
            if (isset($input[$alias])) {
                $data[$name] = $input[$alias];
            }
        }
        return $data;
    }

    /**
     * 组合筛选条件
     * @param array $input 输入数据
     * @param array $where 筛选设置 <string, string, string|null, callable|null, string[]|null>
     *                     ['字段名', '操作符', '值', 'empty' => '验证方式', 'find' => '使用字段别名']
     * @return array
     */
    public function buildWhere($input, $where)
    {
        $data = [];
        foreach ($where as $key => $item) {
            if (count($item) >= 2) {
                [$whereField, $op] = $item;
                $inputField = $whereField;
                $inputFields = [];

                if (isset($item['find'])) {
                    $find = $item['find'];
                    if (is_callable($find)) {
                        $inputFields[] = $find($input, $inputField);
                    } elseif (is_array($find)) {
                        $inputFields = array_merge($inputFields, $find);
                    }
                } else {
                    $inputFields[] = $inputField;
                }

                foreach ($inputFields as $field) {
                    if (!isset($input[$field])) {
                        continue;
                    }
                    $condition = $input[$field];
                    if (isset($item['empty'])) {
                        if (is_callable($item['empty']) && !$item['empty']($condition)) {
                            continue;
                        }
                    } else {
                        if (empty($condition)) {
                            continue;
                        }
                    }
                    $parse = $item[2] ?? null;
                    $data[] = [
                        $whereField,
                        $op,
                        (isset($parse) && is_callable($parse)) ? $parse($condition, $field) : $condition,
                    ];
                    break;
                }
            }
        }
        return $data;
    }
}
