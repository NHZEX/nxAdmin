<?php

declare(strict_types=1);

namespace app\Service\Transaction;

class MainTrans extends Transaction
{
    public static function getConnection(): string
    {
        return 'main';
    }
}
