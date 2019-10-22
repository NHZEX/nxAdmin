<?php
/**
 * Created by PhpStorm.
 * User: NHZEXG
 * Date: 2019/1/2
 * Time: 10:04
 */

namespace Tp\Model\Traits;

use InvalidArgumentException;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\Model;
use Tp\Db\Query;
use Tp\Model\Exception\ModelException;

/**
 * Trait OptimLock
 * @package Tp\Model\Traits
 * @mixin Model
 * @method Query wherePk($op, $condition = null) static 指定主键查询条件
 * @property string|false $optimLock
 */
trait OptimLock
{
    protected $optimLock = 'lock_version';

    private $lockVersion = null;

    /**
     * 使用特定锁查询一条数据
     * @param int $id
     * @param int $lock_version
     * @return static|Model|null
     * @throws ModelException
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public static function findOptimisticVer(int $id, int $lock_version)
    {
        $that = (new static());
        $result = $that->wherePk($id)->where($that->optimLock, $lock_version)->find();

        if (false === $result instanceof static) {
            if (1 === (new static())->wherePk($id)->count()) {
                throw new ModelException('The object being updated is outdated.', CODE_MODEL_OPTIMISTIC_LOCK);
            }
        }
        $result[$that->optimLock] = $lock_version;
        return $result;
    }

    /**
     * 获取变化的数据 并排除只读数据
     * TODO 数据类型发生变化时无法分辨数据变更
     * @return array
     */
    public function getChangedData(): array
    {
        /** @noinspection PhpUndefinedClassInspection */
        $data = parent::getChangedData();

        // 移除非字段数据值，降低乐观锁的无效更新
        foreach (array_diff(array_keys($data), $this->getTableFields()) as $k) {
            unset($data[$k]);
        }

        // 无数据需要变更
        if (isset($data[$this->optimLock]) && 1 === count($data)) {
            $data = [];
        }

        return $data;
    }

    /**
     * 获取锁内容
     * @return int|null
     */
    protected function getLockVersion(): ?int
    {
        try {
            $lockVer = $this->getData($this->optimLock);
        } catch (InvalidArgumentException $exception) {
            $lockVer = $this->getOrigin($this->optimLock);
        }

        return is_numeric($lockVer) ? (int) $lockVer : null;
    }

    /**
     * 数据检查
     * @access protected
     * @return void
     */
    protected function checkData(): void
    {
        $this->isExists() ? $this->updateLockVersion() : $this->recordLockVersion();
    }

    /**
     * 记录乐观锁
     * @access protected
     * @return void
     */
    protected function recordLockVersion(): void
    {
        if ($this->optimLock) {
            $this->set($this->optimLock, 0);
        }
    }

    /**
     * 更新乐观锁
     * @access protected
     * @return void
     */
    protected function updateLockVersion(): void
    {
        if ($this->optimLock && null !== ($lockVer = $this->getLockVersion())) {
            // 更新乐观锁
            $this->set($this->optimLock, $lockVer + 1);
            $this->lockVersion = $lockVer;
        }
    }

    /**
     * @return array
     */
    public function getWhere()
    {
        /** @noinspection PhpUndefinedClassInspection */
        $where = parent::getWhere();

        if (!$this->optimLock) {
            return $where;
        }

        if (null !== ($lockVer = $this->getLockVersion())) {
            // 删除数据时乐观锁没有走数据检测流程
            if ($this->lockVersion === null) {
                $this->lockVersion = $lockVer;
            }
            $where[] = [$this->optimLock, '=', $this->lockVersion];
        }

        return $where;
    }

    /**
     * @param $result
     * @throws ModelException
     */
    protected function checkResult($result): void
    {
        if (!$result) {
            throw new ModelException('The object being updated is outdated.', CODE_MODEL_OPTIMISTIC_LOCK);
        }
    }
}
