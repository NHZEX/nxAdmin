<?php

namespace app\Model;

use app\Exception\AccessControl;
use app\Logic\AdminRole as AdminRoleLogic;
use think\Model;
use think\model\concern\SoftDelete;
use Tp\Model\Traits\MysqlJson;

/**
 * Class AdminRole
 *
 * @package app\common\model
 * @property int $id
 * @property int $genre 类型 1=系统 2=代理商
 * @property int $status 状态 0=正常 1=禁用
 * @property int $create_time 创建时间
 * @property int $update_time 更新时间
 * @property int|null $delete_time 删除时间
 * @property string $name 角色名称
 * @property array $auth 权限
 * @property int $lock_version 锁版本
 * @property-read string $status_desc 状态描述
 * @property-read string $genre_desc 类型描述
 * @property string $description 角色描述
 * @property mixed ext 权限
 */
class AdminRole extends Base
{
    use SoftDelete;
    use MysqlJson;

    protected $table = 'admin_role';
    protected $pk = 'id';

    protected $readonly = [
        'genre',
        'create_time',
    ];

    protected $type = [
        'ext' => 'json',
    ];

    const STATUS_NORMAL = 0;
    const STATUS_DISABLE = 1;
    const STATUS_DICT = [
        self::STATUS_NORMAL => '正常',
        self::STATUS_DISABLE => '禁用',
    ];

    const GENRE_SYSTEM = 1;
    const GENRE_AGENT = 2;
    const GENRE_DICT = [
        self::GENRE_SYSTEM => '系统角色',
        self::GENRE_AGENT => '代理角色',
    ];

    const ACCESS_CONTROL = [
        AdminUser::GENRE_SUPER_ADMIN => ['*', self::GENRE_SYSTEM, self::GENRE_AGENT],
        AdminUser::GENRE_ADMIN => ['*', self::GENRE_SYSTEM, self::GENRE_AGENT],
        AdminUser::GENRE_AGENT => [self::GENRE_AGENT],
    ];
    const EXT_PERMISSION = 'permission';
    const EXT_MENU = 'menu';

    /**
     * @param Model $model
     * @return mixed|void
     * @throws AccessControl
     */
    public static function onBeforeInsert(Model $model)
    {
        self::checkAccessControl($model);

        if (empty($model->ext)) {
            $model->setAttr('ext', '{}');
        }
        if (empty($model->description)) {
            $model->setAttr('description', '');
        }
    }

    /**
     * @param Model $model
     * @return mixed|void
     * @throws AccessControl
     */
    public static function onBeforeUpdate(Model $model)
    {
        self::checkAccessControl($model);
    }

    /**
     * @param AdminRole $model
     * @return mixed|void
     * @throws AccessControl
     */
    public static function onBeforeDelete(AdminRole $model)
    {
        self::checkAccessControl($model);
    }

    /**
     * @param AdminRole $model
     */
    public static function onAfterWrite(AdminRole $model)
    {
        AdminRoleLogic::refreshCache($model);
    }

    /**
     * @param AdminRole $model
     */
    public static function onAfterDelete(AdminRole $model)
    {
        AdminRoleLogic::destroyCache($model);
    }

    /**
     * @param Model $data
     * @throws AccessControl
     */
    protected static function checkAccessControl(Model $data)
    {
        // $dataGenre = $data->getOrigin('genre') ?? $data->getData('genre');
        // $dataId = $data->getOrigin('id');
        // $auth = Auth::instance();
        // if (null === $dataGenre || null === ($accessGenre = $auth->user()->genre)) {
        //     return;
        // }
        // $accessId = $auth->id();
        // $genreControl = self::ACCESS_CONTROL[$accessGenre] ?? [];
        // if (false === in_array($dataGenre, $genreControl)) {
        //     throw new AccessControl('当前登陆的用户无该数据的操作权限');
        // }
        // // 当前数据存在ID且数据ID与访问ID不一致 且 当前权限组不具备全组访问权限
        // if ((null !== $dataId && $dataId !== $accessId) && false === in_array('*', $genreControl)) {
        //     throw new AccessControl('当前登陆的用户无该数据的操作权限');
        // }
    }

    /**
     * 获取虚拟列 类型描述
     * @return mixed|string
     */
    protected function getGenreDescAttr()
    {
        return self::GENRE_DICT[$this->getData('genre')] ?? '未知';
    }

    /**
     * 获取虚拟列 状态描述
     * @return mixed|string
     */
    protected function getStatusDescAttr()
    {
        return self::STATUS_DICT[$this->getData('status')] ?? '未知';
    }

    /**
     * 获取角色列表
     * @param array|null    $argv
     * @param callable|null $where
     * @return array
     */
    public static function buildOption(array $argv = null, callable $where = null): array
    {
        return parent::buildOption([
           'id',
            function ($item) {
                return "[{$item['genreDesc']}] {$item['name']}";
            },
           'type' => 'genre',
        ]);
    }
}
