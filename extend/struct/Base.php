<?php
/**
 * Created by PhpStorm.
 * User: NHZEXG
 * Date: 2019/1/24
 * Time: 10:31
 */

namespace struct;

abstract class Base implements \ArrayAccess, \JsonSerializable
{
    protected $hidden_key = [];

    public function __construct(iterable $arr = [])
    {
        foreach ($arr as $key => $value) {
            $this->$key = $value;
        }
    }

    /**
     * 设置需要隐藏的输出值
     * @access public
     * @param  array $hidden 属性列表
     * @return $this
     */
    public function hidden($hidden = []): self
    {
        $this->hidden_key = array_flip($hidden);
        return $this;
    }

    /**
     * 返回该集合内部属性
     * @return array
     */
    public function all(): array
    {
        return $this->getPublicVars();
    }

    /**
     * 把结构对象转换为数组输出
     * @return array
     */
    public function toArray(): array
    {
        $data = $this->getPublicVars();
        // 过滤隐藏值
        if (count($this->hidden_key)) {
            $data  = array_diff_key($data, $this->hidden_key);
        }
        return $data;
    }

    /**
     * 获取类的公开属性
     * @return array
     */
    private function getPublicVars(): array
    {
        /** @var $e */
        $e = new class {
            /**
             * @param $that
             * @return array
             */
            public function read($that): array
            {
                return get_object_vars($that);
            }
        };
        return $e->read($this) ?: [];
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
        return property_exists($this, $offset);
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
        return $this->$offset;
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
        $this->$offset = $value;
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
        $this->$offset = null;
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
