<?php
/**
 * Created by PhpStorm.
 * User: NHZEXG
 * Date: 2019/1/2
 * Time: 10:04
 */

namespace db\traits;

use app\model\Base;
use db\exception\ModelException;
use think\Model;

/**
 * Trait OptimLock
 * @package db\traits
 * @mixin Model
 * @mixin Base
 */
trait OptimLock
{
    protected $optimLock = 'lock_version';

    /**
     * [被动验证] 使用乐观锁查询一条数据
     * @param int $id
     * @param int $lock_version
     * @return static|\PDOStatement|\think\Model|null
     * @throws ModelException
     */
    public static function getOptimisticVer(int $id, int $lock_version)
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
     * [主动验证] 保存当前数据对象
     * @noinspection PhpDocMissingThrowsInspection, PhpDocRedundantThrowsInspection
     * @access public
     * @param  array  $data     数据
     * @param  array  $where    更新条件
     * @param  string $sequence 自增序列名
     * @return bool
     * @throws ModelException
     * TODO 导致模型事件中的 after_update 与 after_write 无法获取当前操作主键值
     */
    public function save($data = [], $where = [], $sequence = null)
    {
        $pk = $this->getPk();
        $pkid = $this[$pk] ?? null;
        $optim_lock = $this->optimLock && isset($this[$this->optimLock]) && $this->isExists() && null !== $pkid;
        $relationWriteCopy = [];

        try {
            static::startTrans();

            if ($optim_lock) {
                // 推迟执行关联更新
                if (!empty($this->relationWrite)) {
                    $relationWriteCopy = $this->relationWrite;
                    $this->relationWrite = [];
                }
                // 构建新的查询参数
                $new_where = [];
                foreach ($this->getWhere() as $item) {
                    $new_where[$item[0]] = $item;
                }
                if (false === isset($new_where[$this->optimLock])) {
                    $new_where[$this->optimLock] = [$this->optimLock, '=', $this->getData($this->optimLock)];
                } else {
                    $new_where[$this->optimLock][2] = $this->getData($this->optimLock);
                }
                $this->isUpdate(true, array_values($new_where));
                // 从数据中移除主键，阻止模型自动检测
                $this->__unset($pk);

                // 锁版本自增
                $this->data($this->optimLock, $this->getData($this->optimLock) + 1);

                $optim_lock = true;
            }
            $result = parent::save($data, $where, $sequence);

            if ($optim_lock) {
                // 检测是否更新成功
                if ($this->getConnection()->getNumRows() === 0) {
                    throw new ModelException('The object being updated is outdated.', CODE_MODEL_OPTIMISTIC_LOCK);
                }
                // 执行被推迟的关联更新
                if (!empty($this->relationWrite = $relationWriteCopy)) {
                    $this->autoRelationUpdate();
                }
                // 还原被销毁的主键
                $this->data($pk, $pkid);
            }

            static::commit();
        } catch (\Exception $exception) {
            /** @noinspection PhpUnhandledExceptionInspection */
            static::rollback();
            /** @noinspection PhpUnhandledExceptionInspection */
            throw $exception;
        }

        return $result;
    }

    /**
     * 获取变化的数据 并排除只读数据
     * @access public
     * @return array
     * @NotEnabled
     */
    public function getChangedDataNotEnabled()
    {
        $currData = $this->getData(null);
        $originData = $this->getOrigin(null);
        if ($this->isForce()) {
            $data = $currData;
        } else {
            $data = array_udiff_assoc($currData, $originData, function ($a, $b) {
                if ((empty($a) || empty($b)) && $a !== $b) {
                    return 1;
                }

                return is_object($a) || $a != $b ? 1 : 0;
            });
        }

        if (!empty($this->readonly)) {
            // 只读字段不允许更新
            foreach ($this->readonly as $key => $field) {
                if (isset($data[$field])) {
                    unset($data[$field]);
                }
            }
        }

        return $data;
    }
}
