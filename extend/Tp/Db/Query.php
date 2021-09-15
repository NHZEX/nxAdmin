<?php

namespace Tp\Db;

use think\Model;
use think\model\Collection;

/**
 * Class Query
 * @package db
 * @method array|Model|null find($data = null) 查询单条记录
 * @method Collection|Model[]|array select($data = null) 查询多个记录
 */
class Query extends \think\db\Query
{
    /**
     * 指定主键查询条件
     * @deprecated 不完善
     * @param  mixed $op        查询表达式
     * @param  mixed $condition 查询条件
     * @return $this
     */
    public function wherePk($op, $condition = null)
    {
        return $this->where($this->getPk(), $op, $condition);
    }
}
