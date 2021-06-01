<?php

namespace app\Service\Auth\Record;

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
}
