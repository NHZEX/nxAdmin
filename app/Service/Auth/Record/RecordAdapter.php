<?php

namespace app\Service\Auth\Record;

use app\Service\Auth\AuthHelper;
use think\Request;
use think\Response;
use Zxin\Think\Auth\AuthContext;
use Zxin\Think\Auth\Record\RecordAdapterInterface;
use Zxin\Think\Auth\Record\RecordContext;
use function env;

class RecordAdapter implements RecordAdapterInterface
{
    public function isActivity(Request $request, Response $response): bool
    {
        if ('OPTIONS' === $request->method(true)) {
            return false;
        }
        if (!env('RECORD_ACCESS_METHOD_GET')
            && 'GET' === $request->method(true)
        ) {
            return false;
        }
        if (null === AuthContext::get()) {
            return false;
        }

        return true;
    }

    public function writeRecord(Request $request, Response $response, ?RecordContext $recordContext, ?AuthContext $authContext): void
    {
        $userId = AuthHelper::id();

        $extra = $recordContext->getExtra();
        $module = $extra['__module'] ?? 'unknown';
        RecordModel::create([
            'user_id' => $userId,
            'module' => $module,
            'target' => $authContext->getFeature()['class'],
            'auth_name' => $authContext->getPermissionsLine() ?? '<super>',
            'method' => $request->method(true),
            'url' => $request->baseUrl(),
            'ip' => $request->ip(),
            'http_code' => $response->getCode(),
            'resp_code' => $recordContext->getCode(),
            'resp_message' => $recordContext->getMessage(),
            'details' => $recordContext->getExtra(),
        ]);
    }
}
