<?php
/**
 * Created by PhpStorm.
 * User: NHZEXG
 * Date: 2018/11/7
 * Time: 12:01
 */

namespace db\traits;

use db\exception\ModelException;
use think\Db;
use think\db\Connection;
use think\Exception;

trait TransactionExtension
{
    /**
     * 当前是否在一个事务内
     * @author NHZEXG
     * @return bool
     */
    public static function theTransaction()
    {
        try {
            $instance = Connection::instance(Db::getConfig())->getPdo();
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
     * @author NHZEXGinTransaction
     * @return bool
     */
    public function inTransaction() :bool
    {
        return self::theTransaction();
    }

    /**
     * 不再事务中执行将直接抛出异常
     * @author NHZEXG
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
     * @author NHZEXG
     * @throws ModelException
     */
    public function inTransactionTryFail() :void
    {
        if (!$this->inTransaction()) {
            throw new ModelException('过程需要再事务中调用', CODE_MODEL_TRANSACTION);
        }
    }
}
