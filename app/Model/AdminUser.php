<?php
/**
 * Created by PhpStorm.
 * User: NHZEXG
 * Date: 2018/10/20
 * Time: 11:00
 */

namespace app\Model;

use app\Exception\AccessControl;
use app\Service\Auth\Contracts\Authenticatable as AuthenticatableContracts;
use app\Service\Auth\Contracts\ProviderlSelfCheck;
use app\Service\Auth\Facade\Auth;
use RuntimeException;
use think\Model;
use think\model\concern\SoftDelete;
use think\model\relation\BelongsTo;
use Tp\Model\Exception\ModelException;

/**
 * Class AdminUser
 *
 * @package app\common\model
 * @property int            $id
 * @property int            $genre
 * @property int            $status 状态：0禁用，1启用
 * @property string         $username 用户名
 * @property string         $nickname 昵称
 * @property string         $password 密码
 * @property string         $email 邮箱地址
 * @property int            $avatar 头像
 * @property int            $role_id 角色ID
 * @property int            $group_id 部门id
 * @property string         $signup_ip 注册ip
 * @property int            $create_time 创建时间
 * @property int            $update_time 更新时间
 * @property int            $last_login_time 最后一次登录时间
 * @property string         $last_login_ip 登录ip
 * @property string         $remember 记住令牌
 * @property int            $lock_version 数据版本
 * @property-read string    $status_desc 状态描述
 * @property-read string    $genre_desc 类型描述
 * @property-read string    $role_name load(beRoleName)
 * @property-read AdminRole $role 用户角色 load(role)
 * @property string|null    $avatar_data
 * @property int            $delete_time 删除时间
 * @property int            $sign_out_time 退出登陆时间
 */
class AdminUser extends Base implements AuthenticatableContracts, ProviderlSelfCheck
{
    use SoftDelete;

    protected $table = 'admin_user';
    protected $pk = 'id';

    protected $readonly = ['genre'];

    protected $hidden = [
        'remember', 'password', 'delete_time'
    ];

    const STATUS_NORMAL = 0;
    const STATUS_DISABLE = 1;
    const STATUS_DICT = [
        self::STATUS_NORMAL => '正常',
        self::STATUS_DISABLE => '禁用',
    ];

    const GENRE_SUPER_ADMIN = 1;
    const GENRE_ADMIN = 2;
    const GENRE_AGENT = 3;
    const GENRE_DICT = [
        self::GENRE_SUPER_ADMIN => '超级管理员',
        self::GENRE_ADMIN => '管理员',
        self::GENRE_AGENT => '代理商',
    ];

    const ACCESS_CONTROL = [
        self::GENRE_SUPER_ADMIN => ['*', self::GENRE_SUPER_ADMIN, self::GENRE_ADMIN, self::GENRE_AGENT],
        self::GENRE_ADMIN => ['*', self::GENRE_ADMIN, self::GENRE_AGENT],
        self::GENRE_AGENT => [self::GENRE_AGENT],
    ];
    const PWD_HASH_ALGORITHM = PASSWORD_DEFAULT;
    const PWD_HASH_OPTIONS = ['cost' => 10];

    protected $permissions = [];

    /**
     * @param AdminUser|Model $model
     * @return mixed|void
     * @throws AccessControl
     * @throws ModelException
     */
    public static function onBeforeInsert(Model $model)
    {
        self::checkAccessControl($model);
        self::checkUserInputUnique($model);

        // 数据填充
        foreach (['signup_ip' => 0, 'last_login_ip' => 0] as $field => $default) {
            if (!$model->hasData($field)) {
                $model->$field = $default;
            }
        }

        // 令牌填充
        $model->remember = get_rand_str(16);
        self::recodeUser($model);
    }

    /**
     * @param AdminUser|Model $model
     * @return mixed|void
     * @throws AccessControl
     * @throws ModelException
     */
    public static function onBeforeUpdate(Model $model)
    {
        self::checkAccessControl($model);
        self::checkUserInputUnique($model);
        self::recodeUser($model);
    }

    /**
     * @param AdminUser $model
     * @return mixed|void
     * @throws AccessControl
     */
    public static function onBeforeDelete(AdminUser $model)
    {
        self::checkAccessControl($model);
    }

    /**
     * @param self $data
     * @throws AccessControl
     */
    protected static function checkAccessControl(AdminUser $data)
    {
        if ($data->isDisableAccessControl()) {
            return;
        }
        $auth = Auth::instance();
        if (!$auth->check()) {
            return;
        }
        $dataGenre = $data->getOrigin('genre') ?? $data->getData('genre');

        $dataId = $data->getOrigin('id');

        if (null === $dataGenre || null === $auth->user()->genre) {
            return;
        }
        $accessGenre = $auth->user()->genre;
        $accessId = $auth->user()->id;
        $genreControl = self::ACCESS_CONTROL[$accessGenre] ?? [];
        // 控制当前用户的组间访问
        if (false === in_array($dataGenre, $genreControl)) {
            throw new AccessControl('当前登陆的用户无该数据的操作权限');
        }
        // 当前数据存在ID且数据ID与访问ID不一致 且 当前权限组不具备全组访问权限
        if ((null !== $dataId && $dataId !== $accessId) && false === in_array('*', $genreControl)) {
            throw new AccessControl('当前登陆的用户无该数据的操作权限');
        }
    }

    /**
     * @param self $data
     * @throws ModelException
     */
    protected static function checkUserInputUnique(AdminUser $data)
    {
        if ($data->hasData('username')
            && $data->getOrigin('username') !== $data->getData('username')
        ) {
            $isExist = static::withTrashed()
                ->where('username', $data->username)
                ->limit(1)->count();
            if ($isExist > 0) {
                throw new ModelException("该账号 {$data->username} 已经存在");
            }
        }
        if ($data->hasData('email')
            && $data->getOrigin('email') !== $data->getData('email')
        ) {
            $isExist = (new static)
                ->where('email', $data->email)
                ->limit(1)->count();
            if ($isExist > 0) {
                throw new ModelException("该邮箱 {$data->email} 已经存在");
            }
        }
    }

    public function isSuperAdmin()
    {
        return self::GENRE_SUPER_ADMIN === $this->genre;
    }

    public function isAdmin()
    {
        return self::GENRE_ADMIN === $this->genre;
    }

    /**
     * @return array
     */
    public function permissions(): ?array
    {
        if (empty($this->permissions)) {
            $roleId = $this->isSuperAdmin() ? -1 : $this->role_id;
            $this->permissions = \app\Logic\AdminRole::queryPermission($roleId);
        }
        return $this->permissions;
    }

    /**
     * @return string
     */
    public function getRememberToken()
    {
        return $this->remember;
    }

    public function updateRememberToken(string $token)
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
        if ($this->role_id && $this->role && AdminRole::STATUS_NORMAL !== $this->role->status) {
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
     * @author NHZEXG
     * @return mixed|string
     */
    protected function getGenreDescAttr()
    {
        return self::GENRE_DICT[$this->getData('genre')] ?? '未知';
    }

    /**
     * 获取器 记住令牌
     * @param null|string $value
     * @return string
     */
    protected function getRememberAttr(?string $value)
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
     * @param $value
     * @return mixed|string|null
     */
    protected function getAvatarAttr($value)
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
     * @param $value
     */
    protected function setAvatarDataAttr($value)
    {
        $this->setAttr('avatar', $value);
    }

    /**
     * 获取虚拟列 状态描述
     * @author NHZEXG
     * @return mixed|string
     */
    protected function getStatusDescAttr()
    {
        return self::STATUS_DICT[$this->getData('status')] ?? '未知';
    }

    /**
     * 指定设置器 生成密码哈希
     * @param string $value
     * @return bool|string
     * @throws RuntimeException
     */
    protected function setPasswordAttr(string $value)
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
        $model = new self();
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
    public function verifyPassword(string $password) :bool
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
}
