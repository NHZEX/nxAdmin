<?php

namespace app\Model;

use app\Exception\AccessControl;
use app\Exception\ModelLogicException;
use app\Service\Auth\AuthHelper;
use app\Traits\Model\ModelAccessLimit;
use RuntimeException;
use think\model\concern\SoftDelete;
use think\model\relation\BelongsTo;
use Zxin\Think\Auth\Contracts\Authenticatable as AuthenticatableContracts;
use Zxin\Think\Auth\Contracts\ProviderlSelfCheck;
use function hash;
use function is_null;
use function password_hash;
use function password_needs_rehash;
use function password_verify;

/**
 * model: 系统用户
 * @property int                    $id
 * @property int                    $genre
 * @property int                    $status          状态：0禁用，1启用
 * @property string                 $username        用户名
 * @property string                 $nickname        昵称
 * @property string                 $password        密码
 * @property string                 $email           邮箱地址
 * @property int                    $avatar          头像
 * @property int                    $role_id         角色ID
 * @property string                 $signup_ip       注册ip
 * @property int                    $create_time     创建时间
 * @property int                    $update_time     更新时间
 * @property int                    $last_login_time 最后一次登录时间
 * @property string                 $last_login_ip   登录ip
 * @property string                 $remember        记住令牌
 * @property int                    $lock_version    数据版本
 * @property-read string            $status_desc     状态描述
 * @property-read string            $genre_desc      类型描述
 * @property-read string            $role_name       load(beRoleName)
 * @property-read AdminRole|null    $role            用户角色 load(role)
 * @property string|null            $avatar_data
 * @property-read int               $delete_time     删除时间
 * @property int                    $sign_out_time   退出登陆时间
 */
class AdminUser extends Base implements AuthenticatableContracts, ProviderlSelfCheck, \app\Contracts\ModelAccessLimit
{
    use SoftDelete;
    use ModelAccessLimit;

    protected $table = 'admin_user';
    protected $pk = 'id';

    protected $readonly = [
        'genre',
        'create_time',
    ];

    protected $hidden = [
        'remember',
        'password',
        'delete_time',
        'role',
    ];

    protected $globalScope = ['accessControl'];

    public const STATUS_NORMAL = 0;
    public const STATUS_DISABLE = 1;
    public const STATUS_DICT = [
        self::STATUS_NORMAL => '正常',
        self::STATUS_DISABLE => '禁用',
    ];

    public const GENRE_SUPER_ADMIN = 1;
    public const GENRE_ADMIN = 2;
    public const GENRE_AGENT = 3;
    public const GENRE_DICT = [
        self::GENRE_SUPER_ADMIN => '超级管理员',
        self::GENRE_ADMIN => '管理员',
        self::GENRE_AGENT => '代理商',
    ];

    public const ACCESS_CONTROL = [
        self::GENRE_SUPER_ADMIN => [
            self::GENRE_SUPER_ADMIN => 'rw',
            self::GENRE_ADMIN => 'rw',
            self::GENRE_AGENT => 'rw'
        ],
        self::GENRE_ADMIN => [self::GENRE_ADMIN => 'r', self::GENRE_AGENT => 'rw', 'self' => 'rw'],
        self::GENRE_AGENT => ['self' => 'r'],
    ];
    public const PWD_HASH_ALGORITHM = PASSWORD_DEFAULT;
    public const PWD_HASH_OPTIONS = ['cost' => 10];

    protected $permissions = [];

    /**
     * @param AdminUser $model
     * @return mixed|void
     * @throws AccessControl
     * @throws ModelLogicException
     */
    public static function onBeforeInsert(AdminUser $model)
    {
        self::checkAccessControl($model);
        self::checkUserInputUnique($model);

        // 数据填充
        foreach (['signup_ip' => '', 'last_login_ip' => ''] as $field => $default) {
            if (!$model->hasData($field)) {
                $model->$field = $default;
            }
        }

        // 令牌填充
        $model->remember = get_rand_str(16);
    }

    /**
     * @param AdminUser $model
     * @return void
     * @throws AccessControl
     * @throws ModelLogicException
     */
    public static function onBeforeUpdate(AdminUser $model)
    {
        self::checkAccessControl($model);
        self::checkUserInputUnique($model);
    }

    /**
     * @param AdminUser $model
     * @return void
     * @throws AccessControl
     */
    public static function onBeforeDelete(AdminUser $model)
    {
        if ($model->isSuperAdmin()
            && self::where('status', self::STATUS_NORMAL)
                ->where('genre', self::GENRE_SUPER_ADMIN)
                ->limit(2)
                ->count() <= 1
        ) {
            throw new ModelLogicException('不允许删除最后一个可用超管，操作被阻止！');
        }
        self::checkAccessControl($model);
    }

    public function getAccessControl(int $genre): ?array
    {
        return self::ACCESS_CONTROL[$genre] ?? null;
    }

    public function getAllowAccessTarget()
    {
        return AuthHelper::id();
    }

    /**
     * @param self $data
     * @throws ModelLogicException
     */
    protected static function checkUserInputUnique(AdminUser $data)
    {
        if ($data->hasData('username')
            && $data->getOrigin('username') !== $data->getData('username')
        ) {
            $isExist = (new self())
                ->where('username', $data->username)
                ->value('id');
            if ($isExist !== null) {
                throw new ModelLogicException("该账号 {$data->username} 已经存在");
            }
        }
        if ($data->hasData('email')
            && $data->getOrigin('email') !== $data->getData('email')
        ) {
            $isExist = (new self())
                ->where('email', $data->email)
                ->value('id');
            if ($isExist !== null) {
                throw new ModelLogicException("该邮箱 {$data->email} 已经存在");
            }
        }
    }

    /**
     * @inheritDoc
     */
    public static function getSelfProvider($id)
    {
        return AdminUser::notAccessControl()->find($id);
    }

    public static function notAccessControl()
    {
        return self::withoutGlobalScope(['accessControl']);
    }

    public function isSuperAdmin(): bool
    {
        return self::GENRE_SUPER_ADMIN === $this->genre;
    }

    public function isAdmin(): bool
    {
        return self::GENRE_ADMIN === $this->genre;
    }

    public function isAgent(): bool
    {
        return self::GENRE_AGENT === $this->genre;
    }

    public function getIdentity()
    {
        return $this->id;
    }

    public function isIgnoreAuthentication(): bool
    {
        return $this->isSuperAdmin();
    }

    public function allowPermission(string $permission): bool
    {
        return isset($this->permissions()[$permission]);
    }

    /**
     * @return array
     */
    public function permissions(): array
    {
        if (empty($this->permissions)) {
            $roleId            = $this->isSuperAdmin() ? -1 : $this->role_id;
            $this->permissions = \app\Logic\AdminRole::queryPermission($roleId);
        }
        return $this->permissions;
    }

    public function attachSessionInfo(): array
    {
        return [
            'user_genre'   => $this->genre,
            'user_role_id' => $this->role_id,
        ];
    }

    public function getRememberSecret(): string
    {
        return hash('crc32', $this->password);
    }

    /**
     * @return string
     */
    public function getRememberToken(): string
    {
        return $this->remember;
    }

    public function updateRememberToken(string $token): void
    {
        $this->remember = $token;
        $this->save();
    }

    public function valid(&$message): bool
    {
        if (self::STATUS_NORMAL !== $this->status) {
            $message = "用户状态 [{$this->status_desc}]";
            return false;
        }
        if ($this->role_id && !is_null($this->role) && AdminRole::STATUS_NORMAL !== $this->role->status) {
            $message = "角色状态 [{$this->role->status_desc}]";
            return false;
        }
        return true;
    }

    /**
     * 快捷关联 角色名称
     * @return BelongsTo
     */
    protected function beRoleName()
    {
        return $this->belongsTo(AdminRole::class, 'role_id', 'id')
            ->field(['id', 'name'])
            ->bind([
                'role_name' => 'name',
            ]);
    }

    /**
     * 关联获取 角色对象
     * @return BelongsTo
     */
    protected function role()
    {
        return $this->belongsTo(AdminRole::class, 'role_id', 'id');
    }

    /**
     * 获取器 虚拟列 类型描述
     * @return string
     */
    protected function getGenreDescAttr(): string
    {
        return self::GENRE_DICT[$this->getData('genre')] ?? '未知';
    }

    /**
     * 获取器 记住令牌
     * @param null|string $value
     * @return string
     */
    protected function getRememberAttr(?string $value): ?string
    {
        if (!$value) {
            $value = get_rand_str(16);
            if ($this->isExists()) {
                $this->remember = $value;
                $this->save();
            }
        }
        return $value;
    }

    /**
     * 获取器 获取实际访问路径
     * @param string|null $value
     * @return string|string[]|null
     */
    protected function getAvatarAttr(?string $value)
    {
        if ($value) {
            return Attachment::formatAccessPath($value);
        }
        return '';
    }

    /**
     * 供组件使用
     * @return array|string|null
     */
    protected function getAvatarDataAttr()
    {
        return Attachment::formatForItemPath($this->getData('avatar'));
    }

    /**
     * @param string|null $value
     */
    protected function setAvatarDataAttr($value)
    {
        $this->setAttr('avatar', $value);
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
     * 指定设置器 生成密码哈希
     * @param string $value
     * @return string
     * @throws RuntimeException
     */
    protected function setPasswordAttr(string $value): string
    {
        $new_password = password_hash($value, self::PWD_HASH_ALGORITHM, self::PWD_HASH_OPTIONS);

        if (!$new_password) {
            throw new RuntimeException('创建密码哈希失败');
        }

        return $new_password;
    }

    /**
     * 创建用户
     * @param string $username
     * @param string $password
     * @return AdminUser
     */
    public static function createUser(
        string $username,
        string $password
    ) {
        $model           = new self();
        $model->username = $username;
        $model->password = $password;
        $model->save();
        return $model;
    }

    /**
     * 验证密码是否正确
     * @param string $password
     * @return bool
     */
    public function verifyPassword(string $password): bool
    {
        $verify_result = password_verify($password, $this->password);
        if ($verify_result) {
            $password_need_rehash = password_needs_rehash(
                $this->password,
                self::PWD_HASH_ALGORITHM,
                self::PWD_HASH_OPTIONS
            );
            $password_need_rehash && $this->password = $password;
        }
        return $verify_result;
    }

    /**
     * @param array $userIds
     * @return array
     */
    public static function queryUsernamesIgnoreIsolation(array $userIds): array
    {
        $userIds = array_filter(array_unique($userIds));
        return (new AdminUser)
            ->withoutGlobalScope()
            ->whereIn('id', $userIds)
            ->column('username', 'id');
    }
}
