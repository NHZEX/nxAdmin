<?php

namespace Tp\Model\Traits;

use think\Exception;
use think\facade\App;
use Tp\Model\Exception\ModelException;

trait TransactionExtension
{
    /**
     * 当前是否在一个事务内
     * @return bool
     */
    public static function theTransaction()
    {
        try {
            $db = App::getInstance()->db;
            $instance = $db->instance($db->getConfig())->getPdo();
        } catch (Exception $e) {
            // 处理连接类 \InvalidArgumentException 异常
            return false;
        }
        if (false === $instance) {
            return false;
        }
        return $instance->inTransaction();
    }

    /**
     * 当前是否在一个事务内
     * @return bool
     */
    public function inTransaction() :bool
    {
        return self::theTransaction();
    }

    /**
     * 不再事务中执行将直接抛出异常
     * @throws ModelException
     */
    public static function theTransactionTryFail() :void
    {
        if (!self::theTransaction()) {
            throw new ModelException('过程需要再事务中调用', CODE_MODEL_TRANSACTION);
        }
    }

    /**
     * 不再事务中执行将直接抛出异常
     * @throws ModelException
     */
    public function inTransactionTryFail() :void
    {
        if (!$this->inTransaction()) {
            throw new ModelException('过程需要再事务中调用', CODE_MODEL_TRANSACTION);
        }
    }
}
