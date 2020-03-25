<?php
/**
 * Created by PhpStorm.
 * User: NHZEXG
 * Date: 2019/1/5
 * Time: 10:46
 */

namespace app\Middleware;

use app\Traits\CsrfHelper;
use app\Validate\Base;
use app\Validate\VailAsk;
use Closure;
use think\App;
use think\Request;
use think\Response;
use function func\reply\reply_bad;

class Validate extends Middleware
{
    use CsrfHelper;

    /** @var array 验证器映射 */
    protected $mapping = [];

    public static function __make(App $app)
    {
        $that = new self($app);

        if (file_exists($path = $app->getAppPath() . 'validate.php')) {
            /** @noinspection PhpIncludeInspection */
            $that->mapping = require $path;
        }

        return $that;
    }

    /**
     * @param Request  $request
     * @param Closure $next
     * @return Response
     */
    public function handle(Request $request, Closure $next)
    {
        $currClass = $this->getControllerClassName($request);
        $currAction = $request->action(true);

        $validate_cfg = array_change_key_case($this->mapping[$currClass] ?? [])[$currAction] ?? false;
        if (is_array($validate_cfg)) {
            // 获取验证配置
            $validate_cfg = array_pad($validate_cfg, 3, null);
            [$validate_csrf, $validate_class, $validate_scene] = $validate_cfg;

            // 验证输入数据
            if ($validate_class && class_exists($validate_class)) {
                /** @var \think\Validate|Base $v */
                $v = new $validate_class();
                if ($validate_scene) {
                    // 询问当前使用何种场景
                    if ('?' === $validate_scene && $v instanceof VailAsk) {
                        $validate_scene = $v->askScene($request) ?: false;
                    }
                    // 选中将使用的验证场景
                    $validate_scene && $v->scene($validate_scene);
                }
                if (false === $v->check($request->param())) {
                    $message = is_array($v->getError()) ? join(',', $v->getError()) : $v->getError();
                    return reply_bad(CODE_COM_PARAM, $message);
                }
                $request->withMiddleware([
                    'allow_input_fields' => $v->getRuleKeys(),
                ]);
            }

            // 验证CSRF令牌
            if ($validate_csrf) {
                $csrf_update = $request->header(CSRF_TOKEN, false);
                if (false === $this->verifyCsrfToken($csrf_update)) {
                    return reply_bad(CODE_COM_CSRF_INVALID, '令牌无效，请重新访问编辑页面');
                }
            }
        }

        return $next($request);
    }
}
