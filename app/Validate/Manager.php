<?php
/**
 * Created by PhpStorm.
 * User: NHZEXG
 * Date: 2018/11/12
 * Time: 16:38
 * @noinspection PhpUnusedParameterInspection
 */

namespace app\Validate;

use app\controller\admin\Manager as ManagerController;
use app\Model\AdminUser;
use app\Service\Auth\Facade\Auth;
use think\Request;

class Manager extends Base implements VailAsk
{
    protected $rule = [
        'username' => 'require|length:3,64',
        'nickname' => 'require|length:3,64',
        'password' => 'require|length:6,64',
        'role_id' => 'number',
        'status' => 'require|inStatus',

        'page' => 'number',
        'limit' => 'number',
        'type' => 'require|checkFilterType',
    ];

    // 验证字段描述
    protected $field = [
        'password' => '密码',
        'username' => '账号',
        'nickname' => '昵称',
        'email' => '邮箱',
        'phone' => '手机',
    ];

    protected $scene = [
        'table' => [
            'page', 'limit', 'type'
        ],
        'create' => [
            'username', 'nickname', 'password', 'role_id', 'status'
        ],
        'update' => [
            'nickname', 'role_id', 'status'
        ],
        'password' => [
            'password'
        ],
        'pageadd' => ['mark', 'type'],
        'pageedit' => ['id', 'mark'],
        'delete' => ['id'],
    ];

    /**
     * 验证筛选参数：数据类型
     * @param string $value
     * @param string $rule
     * @param array $param
     * @param string $field
     * @param string $desc
     * @return string
     */
    protected function checkFilterType($value, $rule, $param, $field, $desc)
    {
        if (!is_string($value)) {
            return "{$desc} 数据类型错误";
        }
        $isVali = isset(ManagerController::FILTER_TYPE[Auth::user()->genre][$value]);
        return $isVali ?: "{$desc} 内容无效: {$value}";
    }

    protected function inStatus($value)
    {
        return isset(AdminUser::STATUS_DICT[$value]);
    }

    /**
     * @param Request $request
     * @return string|null
     */
    public static function askScene(Request $request): ?string
    {
        if (false !== strpos($request->header(CSRF_TOKEN, false), '.update')) {
            if ('password' === $request->param('action', false)) {
                return 'password';
            }
            return 'update';
        }
        return 'create';
    }
}
