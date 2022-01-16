<?php

namespace app\Model;

use app\Exception\AccessControl;
use app\Logic\AdminRole as AdminRoleLogic;
use app\Service\Auth\AuthHelper;
use app\Traits\Model\ModelAccessLimit;
use stdClass;
use think\model\concern\SoftDelete;
use Tp\Model\Traits\MysqlJson;

/**
 * model: 系统角色
 * @property int         $id
 * @property int         $genre        类型 1=系统 2=代理商
 * @property int         $status       状态 0=正常 1=禁用
 * @property int         $create_time  创建时间
 * @property int         $update_time  更新时间
 * @property int|null    $delete_time  删除时间
 * @property string      $name         角色名称
 * @property array       $auth         权限
 * @property int         $lock_version 锁版本
 * @property-read string $status_desc  状态描述
 * @property-read string $genre_desc   类型描述
 * @property string      $description  角色描述
 * @property array|null  $ext          扩展信息
 */
class AdminRole extends Base implements \app\Contracts\ModelAccessLimit
{
    use SoftDelete;
    use MysqlJson;
    use ModelAccessLimit;

    protected $table = 'admin_role';
    protected $pk = 'id';

    protected $readonly = [
        'genre',
        'create_time',
    ];

    protected $type = [
        'ext' => 'json',
    ];

    protected $globalScope = ['accessControl'];

    public const STATUS_NORMAL = 0;
    public const STATUS_DISABLE = 1;
    public const STATUS_DICT = [
        self::STATUS_NORMAL => '正常',
        self::STATUS_DISABLE => '禁用',
    ];

    public const GENRE_SYSTEM = 1;
    public const GENRE_AGENT = 2;
    public const GENRE_DICT = [
        self::GENRE_SYSTEM => '系统角色',
        self::GENRE_AGENT => '代理角色',
    ];

    public const ACCESS_CONTROL = [
        AdminUser::GENRE_SUPER_ADMIN => [self::GENRE_SYSTEM => 'rw', self::GENRE_AGENT => 'rw'],
        AdminUser::GENRE_ADMIN => [self::GENRE_SYSTEM => 'r', self::GENRE_AGENT => 'rw', 'self' => 'r'],
        AdminUser::GENRE_AGENT => ['self' => 'r'],
    ];

    public const EXT_PERMISSION = 'permission';
    public const EXT_MENU = 'menu';
    public const EXT_AGENT = 'agent';

    /**
     * @param AdminRole $model
     * @return void
     * @throws AccessControl
     */
    public static function onBeforeInsert(AdminRole $model)
    {
        self::checkAccessControl($model);

        if (empty($model->ext)) {
            $model->setAttr('ext', new stdClass());
        }
        if (empty($model->description)) {
            $model->setAttr('description', '');
        }
    }

    /**
     * @param AdminRole $model
     * @return mixed|void
     * @throws AccessControl
     */
    public static function onBeforeUpdate(AdminRole $model)
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
     * @param int $genre
     * @return array|null
     */
    public function getAccessControl(int $genre): ?array
    {
        return self::ACCESS_CONTROL[$genre] ?? null;
    }

    /**
     * @return int|null
     */
    public function getAllowAccessTarget(): ?int
    {
        return AuthHelper::userRoleId();
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
     * @param callable|null $dbCallback
     * @return array
     */
    public static function buildOption(?array $argv = null, callable $where = null, callable $dbCallback = null): array
    {
        return parent::buildOption([
            'id',
            fn ($item) => "[{$item['genreDesc']}] {$item['name']}",
            'type' => 'genre',
        ], $where);
    }
}
