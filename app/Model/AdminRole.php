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
 * model: 系统角色.
 *
 * @property int        $id
 * @property int        $pid
 * @property int        $genre        类型 1=系统 2=代理商
 * @property int        $status       状态 0=正常 1=禁用
 * @property int        $create_time  创建时间
 * @property int        $update_time  更新时间
 * @property int|null   $delete_time  删除时间
 * @property string     $name         角色名称
 * @property string     $description  角色描述
 * @property array|null $ext          扩展信息
 * @property int        $lock_version 锁版本
 * @property array      $auth         权限
 * @property string     $status_desc  状态描述
 * @property string     $genre_desc   类型描述
 */
class AdminRole extends Base implements \app\Contracts\ModelAccessLimit
{
    use ModelAccessLimit;
    use MysqlJson;
    use SoftDelete;

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
    public const GENRE_OPERATOR = 5;
    public const GENRE_DICT = [
        self::GENRE_SYSTEM => '系统角色',
        // self::GENRE_AGENT => '代理角色',
        self::GENRE_OPERATOR => '操作员角色',
    ];

    public const ACCESS_CONTROL = [
        AdminUser::GENRE_SUPER_ADMIN => [self::GENRE_SYSTEM => 'rw', self::GENRE_OPERATOR => 'rw'],
        AdminUser::GENRE_ADMIN => [self::GENRE_SYSTEM => 'r', self::GENRE_OPERATOR => 'rw', 'self' => 'r'],
        // AdminUser::GENRE_AGENT => ['self' => 'r'],
        AdminUser::GENRE_OPERATOR => ['self' => 'r'],
    ];

    public const EXT_PERMISSION = 'permission';
    public const EXT_MENU = 'menu';
    public const EXT_AGENT = 'agent';

    /**
     * @throws AccessControl
     */
    public static function onBeforeInsert(self $model): void
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
     * @return mixed|void
     *
     * @throws AccessControl
     */
    public static function onBeforeUpdate(self $model)
    {
        self::checkAccessControl($model);
    }

    /**
     * @return mixed|void
     *
     * @throws AccessControl
     */
    public static function onBeforeDelete(self $model)
    {
        self::checkAccessControl($model);
    }

    public static function onAfterWrite(self $model): void
    {
        AdminRoleLogic::refreshCache($model);
    }

    public static function onAfterDelete(self $model): void
    {
        AdminRoleLogic::destroyCache($model);
    }

    public function getAccessControl(int $genre): ?array
    {
        return self::ACCESS_CONTROL[$genre] ?? null;
    }

    public function getAllowAccessTarget(): ?int
    {
        return AuthHelper::userRoleId();
    }

    /**
     * 获取虚拟列 类型描述.
     *
     * @return mixed|string
     */
    protected function getGenreDescAttr()
    {
        return self::GENRE_DICT[$this->getData('genre')] ?? '未知';
    }

    /**
     * 获取虚拟列 状态描述.
     *
     * @return mixed|string
     */
    protected function getStatusDescAttr()
    {
        return self::STATUS_DICT[$this->getData('status')] ?? '未知';
    }

    /**
     * 获取角色列表.
     */
    public static function buildOption(?array $argv = null, ?callable $where = null, ?callable $dbCallback = null): array
    {
        return parent::buildOption([
            'id',
            fn ($item) => "[{$item['genreDesc']}] {$item['name']}",
            'type' => 'genre',
        ], $where);
    }
}
