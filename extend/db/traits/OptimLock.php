<?php
/**
 * Created by PhpStorm.
 * User: NHZEXG
 * Date: 2019/1/2
 * Time: 10:04
 */

namespace db\traits;

use db\exception\ModelException;
use think\Model;

/**
 * Trait OptimLock
 * @package db\traits
 * @mixin Model
 * @method \db\Query wherePk($op, $condition = null) static 指定主键查询条件
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
