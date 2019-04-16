<?php
/**
 * Created by PhpStorm.
 * User: NHZEXG
 * Date: 2019/1/2
 * Time: 10:04
 */

namespace Tp\Model\Traits;

use Exception;
use think\Model;
use Tp\Db\Query;
use Tp\Model\Exception\ModelException;

/**
 * Trait OptimLock
 * @package db\traits
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
     * @param mixed $where
     * @return bool
     * @throws Exception
     */
    protected function updateData($where)
    {
        // 获取必要数据 MOD
        $is_optim_lock = $this->optimLock && isset($this[$this->optimLock]);
        $optimLockValue = $this[$this->optimLock] ?? 0;

        // 自动更新
        $this->autoCompleteData($this->update);

        // 事件回调
        if (false === $this->trigger('before_update')) {
            return false;
        }

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

            return false;
        } elseif ($this->autoWriteTimestamp && $this->updateTime && !isset($data[$this->updateTime])) {
            // 自动写入更新时间
            $data[$this->updateTime] = $this->autoWriteTimestamp($this->updateTime);

            $this->data($this->updateTime, $data[$this->updateTime]);
        }

        // 自增锁版本 MOD
        if ($is_optim_lock) {
            $data[$this->optimLock] = $this->getData($this->optimLock) + 1;
        }

        // 尝试获取查询条件
        if (empty($where) && !empty($tmpWhere = $this->getWhere())) {
            $where = $tmpWhere;
        }

        // 检查允许字段
        $allowFields = $this->checkAllowFields(array_merge($this->auto, $this->update));

        // 保留主键数据
        foreach ($this->getData() as $key => $val) {
            if ($this->isPk($key)) {
                $data[$key] = $val;
            }
        }

        $pk    = $this->getPk();
        $array = [];

        foreach ((array) $pk as $key) {
            if (isset($data[$key])) {
                $array[] = [$key, '=', $data[$key]];
                unset($data[$key]);
            }
        }

        if (!empty($array)) {
            $where = $array;
        }

        foreach ((array) $this->relationWrite as $name => $val) {
            if (is_array($val)) {
                foreach ($val as $key) {
                    if (isset($data[$key])) {
                        unset($data[$key]);
                    }
                }
            }
        }

        // 模型更新
        $db = $this->db(false);
        $db->startTrans();

        try {
            $tmp = $db->where($where);
            // 设置乐观锁条件 MOD
            $is_optim_lock && $tmp->where($this->optimLock, '=', $optimLockValue);
            $tmp->strict(false)
                ->field($allowFields)
                ->update($data);

            // 检测是否更新成功 MOD
            if ($is_optim_lock && $this->getConnection()->getNumRows() === 0) {
                throw new ModelException('The object being updated is outdated.', CODE_MODEL_OPTIMISTIC_LOCK);
            }

            // 关联更新
            if (!empty($this->relationWrite)) {
                $this->autoRelationUpdate();
            }

            $db->commit();

            // 更新回调
            $this->trigger('after_update');

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
