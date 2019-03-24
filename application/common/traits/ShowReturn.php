<?php
/**
 * Created by PhpStorm.
 * User: NHZEXG
 * Date: 2018/4/19
 * Time: 14:47
 */

namespace app\common\traits;

use db\Paginator2;
use think\Response;

trait ShowReturn
{
    /**
     * 统一返回 被禁止的访问 未经授权(Forbidden)
     * see RFC7231 and RFC7235.
     * @param string $code
     * @param string $msg
     * @param array  $header
     * @author NHZEXG
     * @return Response
     */
    protected static function show403($code = '', $msg = '', $header = [])
    {
        return self::showReturn($code, null, $msg, false, $header, 403);
    }

    /**
     * 统一返回 被拒绝的访问 未经认证(Unauthorized)
     * see RFC7231 and RFC7235.
     * @param string $code
     * @param string $msg
     * @param array  $header
     * @author NHZEXG
     * @return Response
     */
    protected static function show401($code = '', $msg = '', $header = [])
    {
        return self::showReturn($code, null, $msg, false, $header, 401);
    }

    /**
     * 统一返回 普通
     * Power: Mikkle
     * @param string $code
     * @param array  $data
     * @param string $msg
     * @param array  $header
     * @return Response
     */
    protected static function showData($code = '', $data = null, $msg = '', $header = [])
    {
        return self::showReturn($code, $data, $msg, false, $header);
    }

    /**
     * 统一返回 只返回消息
     * @param string $code
     * @param string $msg
     * @param array  $header
     * @author NHZEXG
     * @return Response
     */
    protected static function showMsg($code = '', $msg = '', $header = [])
    {
        return self::showReturn($code, null, $msg, false, $header);
    }

    /**
     * 统一返回 返回简单异常
     * @param \Exception $exception
     * @param string     $msg
     * @param array      $header
     * @return Response
     * @author NHZEXG
     */
    protected static function showException(\Exception $exception, ?string $msg = null, $header = [])
    {
        return self::showReturn(
            $exception->getCode(),
            null,
            $msg ?? $exception->getMessage(),
            false,
            $header
        );
    }

    /**
     * 统一返回 可拓展格式 (表格用)
     * @param array|\think\Collection|\think\Paginator|Paginator2 $data
     * @param int                                                 $code
     * @param string                                              $msg
     * @return Response
     */
    protected static function showTable($data = null, int $code = CODE_SUCCEED, string $msg = '')
    {
        if ($data instanceof \think\Collection) {
            $result = $data->toArray();
        } elseif ($data instanceof \think\Paginator || $data instanceof Paginator2) {
            $result = [
                'data' => $data->getCollection()->toArray(),
                'count' => $data->total(),
            ];
        } else {
            $result = $data;
        }

        return self::showReturn($code, $result, $msg, true);
    }

    /**
     * 统一返回 可拓展格式
     * @param string $code
     * @param array  $data
     * @param string $msg
     * @return Response
     */
    protected static function showExpand($code = '', $data = null, $msg = '')
    {
        return self::showReturn($code, $data, $msg, true);
    }

    /**
     * 构造返回结果
     * @param string       $code 状态码 务必提交
     * @param array|string $data 返回数据 返回的数据
     * @param string|null  $msg  状态消息 可为空
     * @param bool         $merge
     * @param array        $header
     * @param int          $http_code
     * @param array        $options
     * @return Response
     */
    private static function showReturn(
        $code = null,
        $data = null,
        $msg = '',
        $merge = false,
        $header = [],
        $http_code = 200,
        $options = []
    ) {
        $defult = [
            'code' => $code ?? -1,
            'msg' => 'undefined',
        ];

        if ($merge && is_array($data)) {
            foreach ($data as $key => $value) {
                $defult[$key] = $value;
            }
        } else {
            $defult['data'] = $data;
        }
        //空消息处理
        if (!empty($msg)) {
            $defult['msg'] = $msg;
        } elseif (CODE_DICT[$code] ?? false) {
            $defult['msg'] = CODE_DICT[$code];
        }

        return Response::create($defult, 'json', $http_code, $header, $options);
    }
}
