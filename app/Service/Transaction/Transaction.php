<?php

declare(strict_types=1);

namespace app\Service\Transaction;

use think\App;
use think\db\PDOConnection;
use Tp\Model\Exception\ModelException;
use function get_class;

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

    /**
     * 是否在事务内
     * @return bool
     * @throws ModelException
     */
    public static function inTransaction(): bool
    {
        $db = App::getInstance()->db;
        $connection = $db->connect(static::getConnection());
        if (!$connection instanceof PDOConnection) {
            throw new ModelException('不支持的连接驱动: ' . get_class($connection), CODE_MODEL_TRANSACTION);
        }
        $instance = $connection->getPdo();
        if (false === $instance) {
            return false;
        }
        return $instance->inTransaction();
    }

    /**
     * 不再事务中执行将直接抛出异常
     * @throws ModelException
     */
    public static function tryInTransaction(): void
    {
        if (!self::inTransaction()) {
            throw new ModelException('过程需要再事务中调用', CODE_MODEL_TRANSACTION);
        }
    }
}
