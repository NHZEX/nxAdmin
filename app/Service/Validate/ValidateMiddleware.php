<?php

namespace app\Service\Validate;

use app\Middleware\Middleware;
use app\Traits\CsrfHelper;
use Closure;
use think\App;
use think\Request;
use think\Response;
use think\Validate;
use function func\reply\reply_bad;

class ValidateMiddleware extends Middleware
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
        $controllerClass = $this->getControllerClassName($request);
        $controllerAction = $request->action(true);

        if (!isset($this->mapping[$controllerClass])) {
            return $next($request);
        }
        $validateCfg = array_change_key_case($this->mapping[$controllerClass])[$controllerAction] ?? false;
        if (is_array($validateCfg)) {
            // 解析验证配置
            $validateCfg = array_pad($validateCfg, 3, null);
            if (is_string($validateCfg[0])) {
                [$validateClass, $validateScene] = $validateCfg;
            } else {
                [$validate_csrf, $validateClass, $validateScene] = $validateCfg;
            }

            // 验证输入数据
            if ($validateClass && class_exists($validateClass)) {
                /** @var Validate|ValidateBase $v */
                $v = new $validateClass();
                if ($validateScene) {
                    // 自行决定使用何种场景
                    if ('?' === $validateScene && $v instanceof AskSceneInterface) {
                        $validateScene = $v->askScene($request) ?: false;
                    }
                    // 选中验证场景
                    $validateScene && $v->scene($validateScene);
                }
                $input = $request->param();
                if ($files = $request->file()) {
                    $input += $files;
                }
                if (false === $v->check($input)) {
                    $message = is_array($v->getError()) ? join(',', $v->getError()) : $v->getError();
                    return reply_bad(CODE_COM_PARAM, $message);
                }
                $request->withMiddleware([
                    'allow_input_fields' => $v->getRuleKeys(),
                ]);
            }

            // 验证CSRF令牌 todo 待重构
            if ($validate_csrf ?? false) {
                $csrf_update = $request->header(CSRF_TOKEN, false);
                if (false === $this->verifyCsrfToken($csrf_update)) {
                    return reply_bad(CODE_COM_CSRF_INVALID, '令牌无效，请重新访问编辑页面');
                }
            }
        }

        return $next($request);
    }
}
