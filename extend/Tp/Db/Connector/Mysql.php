<?php

namespace Tp\Db\Connector;

use Throwable;
use Tp\Db\TransactionException;
use function is_callable;

class Mysql extends \think\db\connector\Mysql
{
    public function transaction(callable $callback)
    {
        $this->startTrans();

        try {
            $result = null;
            if (is_callable($callback)) {
                $result = $callback($this);
            }

            $this->commit();
            return $result;
        } catch (Throwable $parentException) {
            try {
                $this->rollback();
            } catch (Throwable $dbException) {
                throw new TransactionException(
                    "rollback fail: {$dbException->getMessage()}",
                    $dbException->getCode(),
                    $parentException
                );
            }
            throw $parentException;
        }
    }
}
