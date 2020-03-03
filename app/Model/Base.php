<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/3/15
 * Time: 14:24
 */

namespace app\Model;

use Closure;
use DateTime;
use think\db\Query;
use think\db\Raw;
use think\Model as ThinkModel;
use think\model\Collection;
use Tp\Db\Query as Query2;
use Tp\Model\Traits\ModelUtil;
use Tp\Model\Traits\OptimLock;
use Tp\Model\Traits\RecordUser;
use Tp\Model\Traits\TransactionExtension;

/**
 * Class Base
 * @mixin Query2
 * @package app\model
 * @method Query2 where(mixed $field, string $op = null, mixed $condition = null) static 查询条件
 * @method Query2 whereRaw(string $where, array $bind = [], string $logic = 'AND') static 表达式查询
 * @method Query2 whereExp(string $field, string $condition, array $bind = [], string $logic = 'AND') static 字段表达式查询
 * @method Query2 when($condition, Closure|array $query, Closure|array $otherwise = null) static 条件查询
 * @method Query2 join(mixed $join, mixed $condition = null, string $type = 'INNER', array $bind = []) static JOIN查询
 * @method Query2 view(mixed $join, mixed $field=null, mixed $on=null, string $type='INNER', array $bind=[]) static 视图查询
 * @method Query2 with(array|string $with) static 关联预载入
 * @method Query2 count(string $field) static Count统计查询
 * @method Query2 min(string $field, bool $force = true) static Min统计查询
 * @method Query2 max(string $field, bool $force = true) static Max统计查询
 * @method Query2 sum(string $field) static SUM统计查询
 * @method Query2 avg(string $field) static Avg统计查询
 * @method Query2 field(mixed $field) static 指定查询字段
 * @method Query2 fieldRaw(string $field) static 指定查询字段
 * @method Query2 union($union, bool $all = false) static UNION查询
 * @method Query2 limit(int $offset, int $length = null) static 查询LIMIT
 * @method Query2 order(string|array|Raw $field, string $order = '') static 查询ORDER
 * @method Query2 orderRaw(string $field, array $bind = []) static 查询ORDER
 * @method Query2 cache(mixed $key = null, integer|DateTime $expire = null, string $tag = null) static 查询缓存
 * @method mixed value(string $field) static 获取某个字段的值
 * @method array column(string $field, string $key = '') static 获取某个列的值
 * @method static find(mixed $data = null) static 查询单个记录
 * @method Collection|static[] select(mixed $data = null) static 查询多个记录
 * @method mixed findOrEmpty(mixed $data = null,mixed $with =[],bool $cache= false) static 查询单个记录 不存在则返回空模型
 * @method ThinkModel withAttr($name, callable $callback = null) 设置数据字段获取器
 * @method Query2 wherePk($op, $condition = null) static 指定主键查询条件
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
