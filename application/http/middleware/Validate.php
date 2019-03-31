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
use think\App;
use think\Request;

class Validate extends Middleware
{
    use ShowReturn;
    use CsrfHelper;

    /** @var array 验证器映射 */
    protected $mapping = [];

    public function __construct(App $app)
    {
        $path = $app->getAppPath() . 'validate.php';
        /** @noinspection PhpIncludeInspection */
        $this->mapping = require $path;
    }

    /**
     * @param Request  $request
     * @param \Closure $next
     * @return \think\Response
     */
    public function handle(Request $request, \Closure $next)
    {
        $currClass = $this->getCurrentDispatchClass($request);
        $currAction = $request->action();

        $validate_cfg = array_change_key_case($this->mapping[$currClass] ?? [])[$currAction] ?? false;
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
