<?php


namespace Tp\Model\Traits;


use app\Facade\WebConv;
use app\Model\AdminUser;
use think\Model;
use think\model\relation\BelongsTo;

/**
 * 自动记录用户
 * Trait ModelEvent
 * @package Tp\Model\Traits
 */
trait RecordUser
{
    /** @var bool 是否自动自动记录当前操作用户 */
    protected $recordUser = false;
    /** @var string 创建者UID */
    protected $createBy = 'create_by';
    /** @var string 更新者UID */
    protected $updateBy = 'update_by';

    /**
     * 自动记录操作用户
     * @param self $data
     */
    protected static function recodeUser(Model $data)
    {
        if ($data->recordUser && WebConv::hasInstance()) {
            $conv = WebConv::getSelf();

            // 缺乏必要的字段锁定设置
            if (false === array_search($data->createBy, $data->readonly)) {
                $data->readonly[] = $data->createBy;
            }
            $fields = array_flip($data->getTableFields());
            isset($fields[$data->createBy]) &&
            $data[$data->createBy] = $conv->sess_user_id;
            isset($fields[$data->updateBy]) &&
            $data[$data->updateBy] = $conv->sess_user_id;
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
}