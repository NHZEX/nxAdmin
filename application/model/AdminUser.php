<?php
/**
 * Created by PhpStorm.
 * User: NHZEXG
 * Date: 2018/10/20
 * Time: 11:00
 */

namespace app\model;

use app\exception\AccessControl;
use db\exception\ModelException;
use facade\WebConv;
use think\model\concern\SoftDelete;

/**
 * Class AdminUser
 *
 * @package app\common\model
 * @property int $id
 * @property int $genre
 * @property int $status 状态：0禁用，1启用
 * @property string $username 用户名
 * @property string $nickname 昵称
 * @property string $password 密码
 * @property string $email 邮箱地址
 * @property int $avatar 头像
 * @property int $role_id 角色ID
 * @property int $group_id 部门id
 * @property string $signup_ip 注册ip
 * @property int $create_time 创建时间
 * @property int $update_time 更新时间
 * @property int $last_login_time 最后一次登录时间
 * @property string $last_login_ip 登录ip
 * @property string $remember 记住令牌
 * @property int $lock_version 数据版本
 * @property-read string $status_desc 状态描述
 * @property-read string $genre_desc 类型描述
 * @property-read string $role_name load(beRoleName)
 * @property-read \app\model\AdminRole $role 用户角色 load(role)
 * @property string|null $avatar_data
 * @property int $delete_time 删除时间
 * @property int $sign_out_time 退出登陆时间
 */
class AdminUser extends Base
{
    use SoftDelete;

    protected $table = 'admin_user';
    protected $pk = 'id';

    protected $readonly = ['genre'];

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
        AdminUser::GENRE_SUPER_ADMIN => ['*', self::GENRE_SUPER_ADMIN, self::GENRE_ADMIN, self::GENRE_AGENT],
        AdminUser::GENRE_ADMIN => ['*', self::GENRE_ADMIN, self::GENRE_AGENT],
        AdminUser::GENRE_AGENT => [self::GENRE_AGENT],
    ];
    const PWD_HASH_ALGORITHM = PASSWORD_DEFAULT;
    const PWD_HASH_OPTIONS = ['cost' => 10];

    public static function init()
    {
        $checkAccessControl = function (self $data) {
            if ($data->isDisableAccessControl()) {
                return;
            }
            $dataGenre = $data->getOrigin('genre') ?? $data->getData('genre');
            $dataId = $data->getOrigin('id');
            if (null === $dataGenre || null === WebConv::getSelf()->sess_user_genre) {
                return;
            }
            $accessGenre = WebConv::getSelf()->sess_user_genre;
            $accessId = WebConv::getSelf()->sess_user_id;
            $genreControl = self::ACCESS_CONTROL[$accessGenre] ?? [];
            // 控制当前用户的组间访问
            if (false === in_array($dataGenre, $genreControl)) {
                throw new AccessControl('当前登陆的用户无该数据的操作权限');
            }
            // 当前数据存在ID且数据ID与访问ID不一致 且 当前权限组不具备全组访问权限
            if ((null !== $dataId && $dataId !== $accessId) && false === in_array('*', $genreControl)) {
                throw new AccessControl('当前登陆的用户无该数据的操作权限');
            }
        };
        $checkUserInputUnique = function (self $data) {
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
        };
        self::beforeInsert($checkAccessControl);
        self::beforeUpdate($checkAccessControl);
        self::beforeDelete($checkAccessControl);
        self::beforeInsert($checkUserInputUnique);
        self::beforeUpdate($checkUserInputUnique);

        self::beforeInsert(function (self $data) {
            // 数据填充
            foreach (['signup_ip' => 0, 'last_login_ip' => 0] as $field => $default) {
                if (!$data->hasData($field)) {
                    $data->data($field, $default);
                }
            }
            // 令牌填充
            $data->data('remember', get_rand_str(16));
        });
    }

    /**
     * 快捷关联 角色名称
     * @return \think\model\relation\BelongsTo
     */
    protected function beRoleName()
    {
        return $this->belongsTo(AdminRole::class, 'role_id', 'id')
            ->field(['id', 'name' => 'role_name'])->bind('role_name');
    }

    /**
     * 关联获取 角色对象
     * @return \think\model\relation\BelongsTo
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
     * @throws \db\exception\ModelException
     */
    protected function getRememberAttr(?string $value)
    {
        if (!$value) {
            $value = get_rand_str(16);
            if ($this->isExists()) {
                $this->data('remember', $value);
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
     * @throws \RuntimeException
     */
    protected function setPasswordAttr(string $value)
    {
        $new_password = password_hash($value, self::PWD_HASH_ALGORITHM, self::PWD_HASH_OPTIONS);

        if (!$new_password) {
            throw new \RuntimeException('创建密码哈希失败');
        }

        return $new_password;
    }

    /**
     * 创建用户
     * @param string $username
     * @param string $password
     * @return AdminUser
     * @throws ModelException
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
     * 验证比吗是否正确
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

    /**
     * 根据id获取用户
     * User: Johnson
     * @param $id
     * @return AdminUser|\PDOStatement|\think\Model|null
     * @throws ModelException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getUserByID($id)
    {
        $user = (new self())->find($id);
        if (false === $user instanceof self) {
            throw new ModelException('用户不存在');
        }
        return $user;
    }
}
