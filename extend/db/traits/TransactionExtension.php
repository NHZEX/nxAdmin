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

trait TransactionExtension
{
    /**
     * 当前是否在一个事务内
     * @author NHZEXG
     * @return bool
     * @throws \think\Exception
     */
    public static function theTransaction()
    {
        return Connection::instance(Db::getConfig())->getPdo()->inTransaction();
    }

    /**
     * 当前是否在一个事务内
     * @author NHZEXGinTransaction
     * @return bool
     * @throws \think\Exception
     */
    public function inTransaction() :bool
    {
        return self::theTransaction();
    }

    /**
     * 不再事务中执行将直接抛出异常
     * @author NHZEXG
     * @throws ModelException
     * @throws \think\Exception
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
     * @throws \think\Exception
     */
    public function inTransactionTryFail() :void
    {
        if (!$this->inTransaction()) {
            throw new ModelException('过程需要再事务中调用', CODE_MODEL_TRANSACTION);
        }
    }
}
