<?php
/**
 * Created by PhpStorm.
 * User: NHZEXG
 * Date: 2019/1/5
 * Time: 10:46
 */

namespace app\http\middleware;

use app\common\traits\CsrfHelper;
use app\common\traits\ShowReturn;
use app\controller;
use app\validate as validator;
use think\Request;

class Validate extends Middleware
{
    use ShowReturn;
    use CsrfHelper;

    const VALIDATE_MAPPING = [
        controller\admin\Login::class => [
            'login' => [false, validator\Login::class],
        ],

        controller\admin\Manager::class => [
            'pageedit' => [false, validator\Manager::class, 'pageedit'],
            'save' => [true, validator\Manager::class, '?'],
            'delete' => [true, validator\Manager::class, 'delete'],
        ],
        controller\admin\Role::class => [
            'save' => [true, null, null],
            'permission' => [false, validator\Role::class, 'toPermission'],
            'savepermission' => [false, validator\Role::class, 'permission'],
        ],
        // TODO 菜单验证器
    ];

    /**
     * @param Request  $request
     * @param \Closure $next
     * @return \think\Response
     */
    public function handle(Request $request, \Closure $next)
    {
        $currClass = $this->getCurrentDispatchClass($request);
        $currAction = $request->action();

        $validate_cfg = array_change_key_case(self::VALIDATE_MAPPING[$currClass] ?? [])[$currAction] ?? false;
        if (is_array($validate_cfg)) {
            // 获取验证配置
            $validate_cfg = array_pad($validate_cfg, 3, null);
            [$validate_csrf, $validate_class, $validate_scene] = $validate_cfg;

            // 验证输入数据
            if ($validate_class && class_exists($validate_class)) {
                /** @var \think\Validate $v */
                $v = new $validate_class();
                if ($validate_scene) {
                    // 询问当前使用何种场景
                    if ('?' === $validate_scene && method_exists($validate_class, 'askScene')) {
                        $validate_scene = call_user_func([$validate_class, 'askScene'], $request) ?: false;
                    }
                    // 选中将使用的验证场景
                    $validate_scene && $v->scene($validate_scene);
                }
                if (false === $v->check($request->param())) {
                    return self::showMsg(CODE_COM_PARAM, $v->getError());
                }
            }

            // 验证CSRF令牌
            if ($validate_csrf) {
                $csrf_update = $request->header(CSRF_TOKEN, false);
                if (false === $this->verifyCsrfToken($csrf_update)) {
                    return self::showMsg(CODE_COM_CSRF_INVALID, '令牌无效，请重新访问编辑页面');
                }
            }
        }

        return $next($request);
    }
}
