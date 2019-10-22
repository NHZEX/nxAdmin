<?php
/**
 * Created by PhpStorm.
 * Date: 2019/3/9
 * Time: 18:06
 */

namespace Tp\Model;

class Collection extends \think\model\Collection
{
    /**
     * 取出第一个key
     * @return int|mixed|string
     */
    public function getFirstKey()
    {
        return array_key_first($this->items);
    }

    /**
     * 取出最后一个key
     * @return int|mixed|string|null
     */
    public function getLastKey()
    {
        return array_key_last($this->items);
    }

    /**
     * 是否不为空
     * @return bool
     */
    public function isNotEmpty()
    {
        return !$this->isEmpty();
    }

    /**
     * 对数组按照键名排序
     * @param int $sort_flags
     * @return Collection
     */
    public function ksort(int $sort_flags = SORT_NUMERIC)
    {
        $items = $this->items;
        ksort($items, $sort_flags);

        return new static($items);
    }

    /**
     * 对数组按照键名逆向排序
     * @param int $sort_flags
     * @return Collection
     */
    public function krsort(int $sort_flags = SORT_NUMERIC)
    {
        $items = $this->items;
        krsort($items, $sort_flags);

        return new static($items);
    }
}
