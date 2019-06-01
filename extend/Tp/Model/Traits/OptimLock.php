<?php
/**
 * Created by PhpStorm.
 * User: NHZEXG
 * Date: 2019/1/2
 * Time: 10:04
 */

namespace Tp\Model\Traits;

use Exception;
use PDOStatement;
use think\exception\PDOException;
use think\Model;
use Tp\Db\Query;
use Tp\Model\Exception\ModelException;

/**
 * Trait OptimLock
 * @package Tp\Model\Traits
 * @mixin Model
 * @method Query wherePk($op, $condition = null) static 指定主键查询条件
 */
trait OptimLock
{
    protected $optimLock = 'lock_version';

    /**
     * [被动验证] 使用乐观锁查询一条数据
     * @param int $id
     * @param int $lock_version
     * @return static|PDOStatement|Model|null
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
     * @return bool
     * @throws PDOException
     */
    protected function updateData(): bool
    {
        // 获取必要数据 MOD
        $is_optim_lock = $this->optimLock && isset($this[$this->optimLock]);
        $optimLockValue = $this[$this->optimLock] ?? 0;

        // 事件回调
        if (false === $this->trigger('BeforeUpdate')) {
            return false;
        }

        $this->checkData();

        // 获取有更新的数据
        $data = $this->getChangedData();

        // 移除无效数据 MOD
        foreach (array_diff(array_keys($data), $this->getTableFields()) as $k) {
            unset($data[$k]);
        }

        if (empty($data)) {
            // 关联更新
            if (!empty($this->relationWrite)) {
                $this->autoRelationUpdate();
            }

            return true;
        }

        if ($this->autoWriteTimestamp && $this->updateTime && !isset($data[$this->updateTime])) {
            // 自动写入更新时间
            $data[$this->updateTime]       = $this->autoWriteTimestamp($this->updateTime);
            $this->set($this->updateTime, $data[$this->updateTime]);
        }

        // 检查允许字段
        $allowFields = $this->checkAllowFields();

        foreach ($this->relationWrite as $name => $val) {
            if (!is_array($val)) {
                continue;
            }

            foreach ($val as $key) {
                if (isset($data[$key])) {
                    unset($data[$key]);
                }
            }
        }

        // 模型更新
        $db = $this->db(false);
        $db->startTrans();

        try {
            $where  = $this->getWhere();
            $result = $db->where($where);
            // 设置乐观锁条件 MOD
            $is_optim_lock && $result->where($this->optimLock, '=', $optimLockValue);
            $result->strict(false)
                ->field($allowFields)
                ->update($data);

            // 检测是否更新成功 MOD
            if ($is_optim_lock && count($this->toArray()) === 0) {
                throw new ModelException('The object being updated is outdated.', CODE_MODEL_OPTIMISTIC_LOCK);
            }

            // 关联更新
            if (!empty($this->relationWrite)) {
                $this->autoRelationUpdate();
            }

            $db->commit();

            // 更新回调
            $this->trigger('AfterUpdate');

            return true;
        } catch (Exception $e) {
            $db->rollback();
            throw $e;
        }
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
