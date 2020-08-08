<?php
declare(strict_types=1);

namespace app\Service\Transaction;

class MainTrans extends Transaction
{
    /**
     * @inheritDoc
     */
    public static function getConnection(): string
    {
        return 'main';
    }
}
