<?php
declare(strict_types=1);

namespace app\Service\Transaction;

use think\App;

abstract class Transaction
{
    /**
     * 获取连接名
     * @return string
     */
    abstract public static function getConnection(): string;

    /**
     * 开始事务
     */
    public static function start(): void
    {
        App::getInstance()->db->connect(static::getConnection())->startTrans();
    }

    /**
     * 提交事务
     */
    public static function commit(): void
    {
        App::getInstance()->db->connect(static::getConnection())->commit();
    }

    /**
     * 回滚事务
     */
    public static function rollback(): void
    {
        App::getInstance()->db->connect(static::getConnection())->rollback();
    }

    /**
     * 闭包事务
     * @param callable $callback
     * @return mixed
     */
    public static function callback(callable $callback)
    {
        return App::getInstance()->db->connect(static::getConnection())->transaction($callback);
    }
}
