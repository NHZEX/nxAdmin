<?php

namespace app\Service\Auth\Record;

use app\Service\Auth\AuthHelper;
use think\event\HttpEnd;
use think\Response;
use think\Service;
use Zxin\Think\Auth\AuthContext;
use function app;
use function env;

class RecordService extends Service
{
    public function register(): void
    {
        $this->listen();
    }

    protected function listen()
    {
        $this->app->event->listen(HttpEnd::class, function (Response $response) {
            $request = $this->app->request;

            if ('OPTIONS' === $request->method(true)) {
                return;
            }
            if (!env('RECORD_ACCESS_METHOD_GET')
                && 'GET' === $request->method(true)
            ) {
                return;
            }
            if (null === AuthContext::get()) {
                return;
            }
            $this->createRecord($response);
        });
    }

    protected function createRecord(Response $response)
    {
        $user = AuthHelper::user();
        $authCtx = AuthContext::get();
        $accessCtx = RecordHelper::accessLog();

        $request = app()->request;

        RecordModel::create([
            'user_id' => $user->id,
            'target' => $authCtx->getFeature()['class'],
            'auth_name' => $authCtx->getPermissionsLine() ?? '<super>',
            'method' => $request->method(true),
            'url' => $request->baseUrl(),
            'ip' => $request->ip(),
            'http_code' => $response->getCode(),
            'resp_code' => $accessCtx->getCode(),
            'resp_message' => $accessCtx->getMessage(),
            'details' => null,
        ]);
    }
}
