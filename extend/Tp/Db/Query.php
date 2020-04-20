<?php

namespace Tp\Db;

use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\Model;
use think\model\Collection;
use Tp\Paginator2;

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
     * @param  mixed $op        查询表达式
     * @param  mixed $condition 查询条件
     * @return $this
     */
    public function wherePk($op, $condition = null)
    {
        return $this->where($this->getPk(), $op, $condition);
    }

    /**
     * @param int  $limit
     * @param int  $page
     * @param bool $simple
     * @return Paginator2
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     * @throws DbException
     */
    public function paginate2(int $limit = 10, int $page = 1, bool $simple = false)
    {
        $options = $this->getOptions();
        unset($this->options['order'], $this->options['limit'], $this->options['page'], $this->options['field']);

        $bind    = $this->bind;
        $total = $this->count();
        $results = $this->options($options)->bind($bind)->page($page, $limit)->select();

        return new Paginator2($results, $limit, $page, $total, $simple);
    }
}
