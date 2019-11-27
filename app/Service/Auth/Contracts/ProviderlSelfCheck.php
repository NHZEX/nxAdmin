<?php
declare(strict_types=1);

namespace app\Service\Auth\Contracts;

interface ProviderlSelfCheck
{
    public function valid(&$message): bool;
}
