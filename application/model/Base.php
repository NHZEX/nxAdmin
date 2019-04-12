<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/3/15
 * Time: 14:24
 */

namespace app\model;

use db\exception\ModelException;
use db\Query as Query2;
use db\traits\ModelUtil;
use db\traits\OptimLock;
use Exception;
use facade\WebConv;
use think\Model as ThinkModel;

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
 * @method \think\model\Collection|\static[] select(mixed $data = null) static 查询多个记录
 * @method mixed get(mixed $data = null,mixed $with =[],bool $cache= false) static 查询单个记录 支持关联预载入
 * @method mixed getOrFail(mixed $data = null,mixed $with =[],bool $cache= false) static 查询单个记录 不存在则抛出异常
 * @method mixed findOrEmpty(mixed $data = null,mixed $with =[],bool $cache= false) static 查询单个记录  不存在则返回空模型
 * @method mixed all(mixed $data = null,mixed $with =[],bool $cache= false) static 查询多个记录 支持关联预载入
 * @method ThinkModel withAttr(array $name,\Closure $closure) 动态定义获取器
 * @method Query2 wherePk($op, $condition = null) static 指定主键查询条件
 */
abstract class Base extends ThinkModel
{
    use ModelUtil;
    use OptimLock;

    protected $optimLock = 'lock_version';

    protected $readonly = ['create_by'];
    /** @var bool 是否自动自动记录当前操作用户 */
    protected $recordUser = false;
    /** @var int 软删除字段默认值 */
    protected $defaultSoftDelete = 0;

    /** @var string 创建者UID */
    protected $createBy = 'create_by';
    /** @var string 更新者UID */
    protected $updateBy = 'update_by';

    /**
     * @param mixed $where
     * @return bool
     * @throws Exception
     */
    protected function updateData($where)
    {
        // 获取必要数据 MOD
        $is_optim_lock = $this->optimLock && isset($this[$this->optimLock]);
        $optimLockValue = $this[$this->optimLock] ?? 0;

        // 自动更新
        $this->autoCompleteData($this->update);

        // 事件回调
        if (false === $this->trigger('before_update')) {
            return false;
        }

        // 获取有更新的数据
        $data = $this->getChangedData();
        // 移除无效数据 MOD
        foreach (array_diff(array_keys($data), $this->getTableFields()) as $k) {
            unset($data[$k]);
        }

        if (empty($data)) {
            // 关联更新
            if (!empty($this->relationWrite)) {
                $this->autoRelationUpdate();
            }

            return false;
        } elseif ($this->autoWriteTimestamp && $this->updateTime && !isset($data[$this->updateTime])) {
            // 自动写入更新时间
            $data[$this->updateTime] = $this->autoWriteTimestamp($this->updateTime);

            $this->data($this->updateTime, $data[$this->updateTime]);
        }

        // 自增锁版本 MOD
        if ($is_optim_lock) {
            $data[$this->optimLock] = $this->getData($this->optimLock) + 1;
        }

        // 尝试获取查询条件
        if (empty($where) && !empty($tmpWhere = $this->getWhere())) {
            $where = $tmpWhere;
        }

        // 检查允许字段
        $allowFields = $this->checkAllowFields(array_merge($this->auto, $this->update));

        // 保留主键数据
        foreach ($this->getData() as $key => $val) {
            if ($this->isPk($key)) {
                $data[$key] = $val;
            }
        }

        $pk    = $this->getPk();
        $array = [];

        foreach ((array) $pk as $key) {
            if (isset($data[$key])) {
                $array[] = [$key, '=', $data[$key]];
                unset($data[$key]);
            }
        }

        if (!empty($array)) {
            $where = $array;
        }

        foreach ((array) $this->relationWrite as $name => $val) {
            if (is_array($val)) {
                foreach ($val as $key) {
                    if (isset($data[$key])) {
                        unset($data[$key]);
                    }
                }
            }
        }

        // 模型更新
        $db = $this->db(false);
        $db->startTrans();

        try {
            $tmp = $db->where($where);
            // 设置乐观锁条件 MOD
            $is_optim_lock && $tmp->where($this->optimLock, '=', $optimLockValue);
            $tmp->strict(false)
                ->field($allowFields)
                ->update($data);

            // 检测是否更新成功 MOD
            if ($is_optim_lock && $this->getConnection()->getNumRows() === 0) {
                throw new ModelException('The object being updated is outdated.', CODE_MODEL_OPTIMISTIC_LOCK);
            }

            // 关联更新
            if (!empty($this->relationWrite)) {
                $this->autoRelationUpdate();
            }

            $db->commit();

            // 更新回调
            $this->trigger('after_update');

            return true;
        } catch (Exception $e) {
            $db->rollback();
            throw $e;
        }
    }

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
     * @return \think\model\relation\BelongsTo
     */
    protected function beCreatorName()
    {
        return $this->belongsTo(AdminUser::class, $this->createBy, 'id')
            ->field(['username' => 'creator_name', 'id'])->bind(['creator_name'])->cache(true, 60);
    }

    /**
     * 获取编辑者名称
     * @return \think\model\relation\BelongsTo
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
