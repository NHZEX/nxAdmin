<?php
declare(strict_types=1);

namespace app\Traits;

use think\Response;
use think\response\View;
use function app;
use function is_null;
use function request;
use function strpos;
use function strtolower;
use function url;

trait JumpHelper
{
    /**
     * 操作成功跳转
     * @param mixed  $msg    提示信息
     * @param string $url    跳转的URL地址
     * @param mixed  $data   返回的数据
     * @param int    $wait   跳转等待时间
     * @param array  $header 发送的Header信息
     * @return Response|View
     */
    protected function success($msg = '', string $url = null, $data = '', int $wait = 3, array $header = [])
    {
        if (is_null($url) && is_null($referer = request()->header('referer'))) {
            $url = $referer;
        } elseif ($url) {
            $url = (strpos($url, '://') || 0 === strpos($url, '/')) ? $url : url($url);
        }

        return $this->jump(1, $msg, $url, $data, $wait, $header);
    }

    /**
     * 操作错误跳转
     * @param mixed  $msg    提示信息
     * @param string $url    跳转的URL地址
     * @param mixed  $data   返回的数据
     * @param int    $wait   跳转等待时间
     * @param array  $header 发送的Header信息
     * @return Response|View
     */
    protected function error($msg = '', string $url = null, $data = '', int $wait = 3, array $header = [])
    {
        if (is_null($url)) {
            $url = request()->isAjax() ? '' : 'javascript:history.back(-1);';
        } elseif ($url) {
            $url = (strpos($url, '://') || 0 === strpos($url, '/')) ? $url : url($url);
        }

        return $this->jump(0, $msg, $url, $data, $wait, $header);
    }

    /**
     * @param             $code
     * @param string      $msg
     * @param string|null $url
     * @param string      $data
     * @param int         $wait
     * @param array       $header
     * @return Response|View
     */
    protected function jump($code, $msg = '', $url = null, $data = '', int $wait = 3, array $header = [])
    {
        $result = [
            'code' => $code,
            'msg'  => $msg,
            'data' => $data,
            'url'  => $url,
            'wait' => $wait,
        ];

        $type = (request()->isJson() || request()->isAjax()) ? 'json' : 'html';

        if ('html' == strtolower($type)) {
            /** @var View $response */
            $response = Response::create('/dispatch_jump', 'view');
            $response->assign($result);
        } else {
            $response = Response::create($result, $type)
                ->header($header)
                ->options([
                    'jump_template' => app('config')->get('app.dispatch_error_tmpl')
                ]);
        }

        return $response;
    }
}
