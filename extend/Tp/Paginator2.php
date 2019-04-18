<?php
/**
 * Created by PhpStorm.
 * User: NHZEXG
 * Date: 2018/12/26
 * Time: 13:38
 */

namespace Tp;

use ArrayAccess;
use ArrayIterator;
use Countable;
use DomainException;
use IteratorAggregate;
use JsonSerializable;
use Tp\Model\Collection;
use Traversable;

class Paginator2 implements ArrayAccess, Countable, IteratorAggregate, JsonSerializable
{
    /**
     * 是否简洁模式
     * @var bool
     */
    protected $simple = false;

    /**
     * 数据集
     * @var Collection
     */
    protected $items;

    /**
     * 当前页
     * @var int
     */
    protected $currentPage;

    /**
     * 最后一页
     * @var int
     */
    protected $lastPage;

    /**
     * 数据总数
     * @var int|null
     */
    protected $total;

    /**
     * 每页数量
     * @var int
     */
    protected $listRows;

    /**
     * 是否有下一页
     * @var bool
     */
    protected $hasMore;

    /**
     * PaginatorResult constructor.
     * @param array|Collection $items
     * @param int              $listRows
     * @param int|null         $currentPage
     * @param int|null         $total
     * @param bool             $simple
     */
    public function __construct(
        $items,
        int $listRows,
        ?int $currentPage = null,
        ?int $total = null,
        bool $simple = false
    ) {
        $this->simple   = $simple;
        $this->listRows = $listRows;

        if (false === ($items instanceof Collection)) {
            $items = Collection::make($items);
        }

        if ($simple) {
            $this->currentPage = $this->setCurrentPage($currentPage);
            $this->hasMore     = count($items) > ($this->listRows);
            $items             = $items->slice(0, $this->listRows);
        } else {
            $this->total       = $total;
            $this->lastPage    = (int) ceil($total / $listRows);
            $this->currentPage = $this->setCurrentPage($currentPage);
            $this->hasMore     = $this->currentPage < $this->lastPage;
        }
        $this->items = $items;
    }

    protected function setCurrentPage($currentPage)
    {
        if (!$this->simple && $currentPage > $this->lastPage) {
            return $this->lastPage > 0 ? $this->lastPage : 1;
        }

        return $currentPage;
    }

    public function total()
    {
        if ($this->simple) {
            throw new DomainException('not support total');
        }

        return $this->total;
    }

    public function listRows()
    {
        return $this->listRows;
    }

    public function currentPage()
    {
        return $this->currentPage;
    }

    public function lastPage()
    {
        if ($this->simple) {
            throw new DomainException('not support last');
        }

        return $this->lastPage;
    }

    /**
     * 数据是否足够分页
     * @access public
     * @return bool
     */
    public function hasPages()
    {
        return !(1 == $this->currentPage && !$this->hasMore);
    }

    /**
     * 获取模型数据集
     * @return Collection
     */
    public function getCollection()
    {
        return $this->items;
    }

    /**
     * 数据集是否空
     * @return bool
     */
    public function isEmpty()
    {
        return $this->items->isEmpty();
    }

    /**
     * 给每个元素执行个回调
     *
     * @access public
     * @param  callable $callback
     * @return $this
     */
    public function each(callable $callback)
    {
        foreach ($this->items as $key => $item) {
            $result = $callback($item, $key);

            if (false === $result) {
                break;
            } elseif (!is_object($item)) {
                $this->items[$key] = $result;
            }
        }

        return $this;
    }

    public function toArray()
    {
        try {
            $total = $this->total();
        } catch (DomainException $e) {
            $total = null;
        }

        return [
            'total'     => $total,
            'limit'     => $this->listRows(),
            'curr_page' => $this->currentPage(),
            'last_page' => $this->lastPage,
            'data'      => $this->items->toArray(),
        ];
    }

    /**
     * Retrieve an external iterator
     * @link  https://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     * @since 5.0.0
     */
    public function getIterator()
    {
        return new ArrayIterator($this->items->all());
    }

    /**
     * Whether a offset exists
     * @link  https://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     *                      An offset to check for.
     *                      </p>
     * @return bool true on success or false on failure.
     *                      </p>
     *                      <p>
     *                      The return value will be casted to boolean if non-boolean was returned.
     * @since 5.0.0
     */
    public function offsetExists($offset)
    {
        return $this->items->offsetExists($offset);
    }

    /**
     * Offset to retrieve
     * @link  https://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     *                      The offset to retrieve.
     *                      </p>
     * @return mixed Can return all value types.
     * @since 5.0.0
     */
    public function offsetGet($offset)
    {
        return $this->items->offsetGet($offset);
    }

    /**
     * Offset to set
     * @link  https://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     *                      The offset to assign the value to.
     *                      </p>
     * @param mixed $value  <p>
     *                      The value to set.
     *                      </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetSet($offset, $value)
    {
        $this->items->offsetSet($offset, $value);
    }

    /**
     * Offset to unset
     * @link  https://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     *                      The offset to unset.
     *                      </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetUnset($offset)
    {
        $this->items->offsetUnset($offset);
    }

    /**
     * Count elements of an object
     * @link  https://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * </p>
     * <p>
     * The return value is cast to an integer.
     * @since 5.1.0
     */
    public function count()
    {
        return $this->items->count();
    }

    /**
     * Specify data which should be serialized to JSON
     * @link  https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }
}
