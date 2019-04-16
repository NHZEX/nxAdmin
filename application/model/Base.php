<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/3/15
 * Time: 14:24
 */

namespace app\model;

use app\facade\WebConv;
use Closure;
use think\Model as ThinkModel;
use think\model\Collection;
use think\model\relation\BelongsTo;
use Tp\Db\Query as Query2;
use Tp\Model\Traits\ModelUtil;
use Tp\Model\Traits\OptimLock;
use Tp\Model\Traits\TransactionExtension;

/**
 * Class Base
 * @mixin Query2
 * @package app\model
 * @method Query2 where(mixed $field, string $op = null, mixed $condition = null) static 查询条件
 * @method Query2 whereRaw(string $where, array $bind = []) static 表达式查询
 * @method Query2 whereExp(string $field, string $condition, array $bind = []) static 字段表达式查询
 * @method Query2 when(mixed $condition, mixed $query, mixed $otherwise = null) static 条件查询
 * @method Query2 join(mixed $join, mixed $condition = null, string $type = 'INNER') static JOIN查询
 * @method Query2 view(mixed $join, mixed $field = null, mixed $on = null, string $type = 'INNER') static 视图查询
 * @method Query2 with(mixed $with) static 关联预载入
 * @method Query2 count(string $field) static Count统计查询
 * @method Query2 min(string $field) static Min统计查询
 * @method Query2 max(string $field) static Max统计查询
 * @method Query2 sum(string $field) static SUM统计查询
 * @method Query2 avg(string $field) static Avg统计查询
 * @method Query2 field(mixed $field, boolean $except = false) static 指定查询字段
 * @method Query2 fieldRaw(string $field, array $bind = []) static 指定查询字段
 * @method Query2 union(mixed $union, boolean $all = false) static UNION查询
 * @method Query2 limit(mixed $offset, integer $length = null) static 查询LIMIT
 * @method Query2 order(mixed $field, string $order = null) static 查询ORDER
 * @method Query2 orderRaw(string $field, array $bind = []) static 查询ORDER
 * @method Query2 cache(mixed $key = null , integer $expire = null) static 设置查询缓存
 * @method mixed value(string $field) static 获取某个字段的值
 * @method array column(string $field, string $key = '') static 获取某个列的值
 * @method \static find(mixed $data = null) static 查询单个记录
 * @method Collection|\static[] select(mixed $data = null) static 查询多个记录
 * @method mixed get(mixed $data = null,mixed $with =[],bool $cache= false) static 查询单个记录 支持关联预载入
 * @method mixed getOrFail(mixed $data = null,mixed $with =[],bool $cache= false) static 查询单个记录 不存在则抛出异常
 * @method mixed findOrEmpty(mixed $data = null,mixed $with =[],bool $cache= false) static 查询单个记录  不存在则返回空模型
 * @method mixed all(mixed $data = null,mixed $with =[],bool $cache= false) static 查询多个记录 支持关联预载入
 * @method ThinkModel withAttr(array $name, Closure $closure) 动态定义获取器
 * @method Query2 wherePk($op, $condition = null) static 指定主键查询条件
 */
abstract class Base extends ThinkModel
{
    use ModelUtil;
    use TransactionExtension;
    use OptimLock;

    protected $readonly = ['create_by'];
    /** @var bool 是否自动自动记录当前操作用户 */
    protected $recordUser = false;
    /** @var int 软删除字段默认值 */
    protected $defaultSoftDelete = 0;

    /** @var string 创建者UID */
    protected $createBy = 'create_by';
    /** @var string 更新者UID */
    protected $updateBy = 'update_by';

    // 全局模型初始化追加
    protected function initialize()
    {
        parent::initialize();

        // 自动追加操作人
        if ($this->recordUser && WebConv::hasInstance()) {
            $conv = WebConv::getSelf();
            $record_user = function (self $data) use ($conv) {
                // 缺乏必要的字段锁定设置
                if (false === array_search($this->createBy, $data->readonly)) {
                    $data->readonly[] = $this->createBy;
                }
                $fields = array_flip($data->getTableFields());
                isset($fields[$this->createBy]) && $data->data($this->createBy, $conv->sess_user_id);
                isset($fields[$this->updateBy]) && $data->data($this->updateBy, $conv->sess_user_id);
            };
            static::beforeInsert($record_user);
            static::beforeUpdate($record_user);
        }
    }

    /**
     * 获取创建者名称
     * @return BelongsTo
     */
    protected function beCreatorName()
    {
        return $this->belongsTo(AdminUser::class, $this->createBy, 'id')
            ->field(['username' => 'creator_name', 'id'])->bind(['creator_name'])->cache(true, 60);
    }

    /**
     * 获取编辑者名称
     * @return BelongsTo
     */
    protected function beEditorName()
    {
        return $this->belongsTo(AdminUser::class, $this->updateBy, 'id')
            ->field(['username' => 'editor_name', 'id'])->bind(['editor_name'])->cache(true, 60);
    }

    /**
     * 关闭数据访问控制
     * @param bool $off
     */
    public function turnOffAccessControl(bool $off = true): void
    {
        $this->data('__access_control', !$off);
    }

    /**
     * 是否关闭数据访问控制
     * @return bool
     */
    public function isDisableAccessControl(): bool
    {
        return $this->hasData('__access_control') && false === $this->getData('__access_control');
    }
}
