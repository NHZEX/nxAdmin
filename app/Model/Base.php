<?php

namespace app\Model;

use app\Traits\Model\ModelEvent;
use app\Traits\Model\ModelHelper;
use app\Traits\QuickHelper;
use Generator;
use think\db\Query;
use think\Model as ThinkModel;
use think\model\Collection;
use Tp\Model\Traits\Attribute;
use Tp\Model\Traits\ModelUtil;
use Tp\Model\Traits\OptimLock;
use function defined;

/**
 * @method $this find(mixed $data = null) static 查询单个记录
 * @method $this findOrEmpty(mixed $data = null,mixed $with =[],bool $cache= false) static 查询单个记录 不存在则返回空模型
 * @method Collection|$this[] select(mixed $data = null) static 查询多个记录
 * @method Generator|$this[] cursor($data = null) static 游标查询
 * @method ThinkModel withAttr($name, callable $callback = null) 设置数据字段获取器
 *
 * @method Query field($field)
 * @method Query fieldRaw(string $field)
 *
 * @method mixed transaction(callable $callback) static
 * @method void startTrans() static
 * @method void commit() static
 * @method void rollback() static
 */
abstract class Base extends ThinkModel
{
    use ModelHelper;
    use ModelUtil;
    use OptimLock;
    use ModelEvent;
    use QuickHelper;
    use Attribute;

    public const EVENT_AFTER_READ = 'AfterRead';
    public const EVENT_BEFORE_INSERT = 'BeforeInsert';
    public const EVENT_AFTER_INSERT = 'AfterInsert';
    public const EVENT_BEFORE_UPDATE = 'BeforeUpdate';
    public const EVENT_AFTER_UPDATE = 'AfterUpdate';
    public const EVENT_BEFORE_WRITE = 'BeforeWrite';
    public const EVENT_AFTER_WRITE = 'AfterWrite';
    public const EVENT_BEFORE_DELETE = 'BeforeDelete';
    public const EVENT_AFTER_DELETE = 'AfterDelete';
    public const EVENT_BEFORE_RESTORE = 'BeforeRestore';
    public const EVENT_AFTER_RESTORE = 'AfterRestore';

    public const BUILD_OPTION_ARGV = ['id', 'name'];

    /** @var int 软删除字段默认值 */
    protected $defaultSoftDelete = 0;
    /** @var bool 自动写入时间戳 */
    protected $autoWriteTimestamp = true;

    /**
     * 是否关闭数据访问控制
     * @return bool
     */
    public function isDisableAccessControl(): bool
    {
        return PHP_SAPI === 'cli' && defined('DISABLE_ACCESS_CONTROL');
    }
}
