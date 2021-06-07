<?php

namespace app\Service\Auth\Record;

use Throwable;
use function app;

class RecordHelper
{
    /**
     * @return RecordContext
     */
    public static function accessLog(): RecordContext
    {
        $app = app();

        if (!$app->has(RecordContext::class)) {
            $ctx = new RecordContext();
            $app->instance(RecordContext::class, $ctx);
        }

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $app->get(RecordContext::class);
    }

    /**
     * @param int    $code
     * @param string $message
     * @return RecordContext
     */
    public static function recordInfo(int $code, string $message): RecordContext
    {
        return self::accessLog()->setCode($code)->setMessage($message);
    }

    /**
     * @param Throwable $throwable
     * @return RecordContext
     */
    public static function recordException(Throwable $throwable): RecordContext
    {
        return self::accessLog()->setException($throwable);
    }
}
