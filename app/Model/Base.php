<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/3/15
 * Time: 14:24
 */

namespace app\Model;

use Closure;
use Generator;
use think\db\Query;
use think\Model as ThinkModel;
use think\model\Collection;
use Tp\Model\Traits\ModelUtil;
use Tp\Model\Traits\OptimLock;
use Tp\Model\Traits\RecordUser;
use Tp\Model\Traits\TransactionExtension;

/**
 * Class Base
 * @package app\model
 * @method $this find(mixed $data = null) static 查询单个记录
 * @method $this findOrEmpty(mixed $data = null,mixed $with =[],bool $cache= false) static 查询单个记录 不存在则返回空模型
 * @method Collection|$this[] select(mixed $data = null) static 查询多个记录
 * @method Generator|$this[] cursor($data = null) static 游标查询
 * @method ThinkModel withAttr($name, callable $callback = null) 设置数据字段获取器
 *
 * @method mixed transaction(callable $callback) static
 */
abstract class Base extends ThinkModel
{
    use ModelUtil;
    use TransactionExtension;
    use OptimLock;
    use RecordUser;

    protected $readonly = ['create_by'];
    /** @var int 软删除字段默认值 */
    protected $defaultSoftDelete = 0;
    /** @var bool 自动写入时间戳 */
    protected $autoWriteTimestamp = true;

    public static function onBeforeInsert(ThinkModel $model)
    {
        self::recodeUser($model);
    }

    public static function onBeforeUpdate(ThinkModel $model)
    {
        self::recodeUser($model);
    }

    /**
     * 是否关闭数据访问控制
     * @return bool
     */
    public function isDisableAccessControl(): bool
    {
        return PHP_SAPI === 'cli' && defined('DISABLE_ACCESS_CONTROL');
    }

    /**
     * 构建子查询
     * @param Closure $closure
     * @param string|null $field
     * @return Closure
     */
    public static function subQuery(Closure $closure, ?string $field)
    {
        return function (Query $query) use ($closure, $field) {
            $query->table((new static())->getTable());
            $closure($query);

            if (!empty($field)) {
                $query->field($field);
            }
        };
    }
}
