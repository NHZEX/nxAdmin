<?php
namespace DbMigrations;

use Phinx\Blueprint as B;
use Phinx\Migration\AbstractMigration;

class Init extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    addCustomColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Any other destructive changes will result in an error when trying to
     * rollback the migration.Migration has pending actions after execution!
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {
        $table = B::table()->id(false)->primaryKey('label')->comment('系统表')->d();
        $system = $this->table('system', $table);
        $system
            ->addColumn(B::string('label', 48)->comment('标签')->d())
            ->addColumn(B::string('value', 255)->comment('值')->d())
            ->create();

        $admin_role = $this->table('admin_role', B::table()->comment('系统角色')->unsigned()->d());
        $admin_role
            ->addColumn(B::genre()->comment('角色类型')->d())
            ->addColumn(B::status()->comment('角色状态')->d())
            ->addColumn(B::createTime()->d())
            ->addColumn(B::updateTime()->d())
            ->addColumn(B::deleteTime()->d())
            ->addColumn(B::string('name', 32)->comment('角色名称')->d())
            ->addColumn(B::string('description', 128)->comment('角色描述')->d())
            ->addColumn(B::json('ext')->comment('角色权限')->d())
            ->addColumn(B::lockVersion()->d())
            ->create();

        $admin_user = $this->table('admin_user', B::table()->comment('系统用户')->unsigned()->d());
        $admin_user
            ->addColumn(B::genre()->comment('用户类型')->d())
            ->addColumn(B::status()->comment('用户状态')->d())
            ->addColumn(B::string('username', 32)->comment('用户账户')->d())
            ->addColumn(B::string('nickname', 32)->comment('用户昵称')->d())
            ->addColumn(B::string('password', 255)->comment('用户密码')->d())
            ->addColumn(B::string('email', 64)->nullable(true)->comment('用户邮箱')->d())
            ->addColumn(B::string('avatar', 96)->nullable(true)->comment('用户头像')->d())
            ->addColumn(B::unsignedInteger('role_id')->comment('角色ID')->d())
            ->addColumn(B::unsignedInteger('group_id')->comment('部门ID')->d())
            ->addColumn(B::string('signup_ip', 46)->ccAscii()->comment('注册IP')->d())
            ->addColumn(B::createTime()->d())
            ->addColumn(B::updateTime()->d())
            ->addColumn(B::deleteTime()->d())
            ->addColumn(B::unsignedInteger('last_login_time')->comment('最后登录时间')->d())
            ->addColumn(B::string('last_login_ip', 46)->ccAscii()->comment('登录ip')->d())
            ->addColumn(B::string('remember', 16)->ccAscii()->comment('登录ip')->d())
            ->addColumn(B::lockVersion()->d())
            ->addIndex('username', B::index()->unique()->d())
            ->create();

        $attachment = $this->table('attachment', B::table()->comment('附件管理')->unsigned()->d());
        $attachment
            ->addColumn(B::status()->d())
            ->addColumn(B::string('driver', 16)->ccAscii()->comment('文件驱动')->d())
            ->addColumn(B::string('index', 64)->ccAscii()->comment('文件索引')->d())
            ->addColumn(B::unsignedInteger('uid')->comment('操作用户')->d())
            ->addColumn(B::string('path', 255)->comment('文件路径')->d())
            ->addColumn(B::string('mime', 128)->ccAscii()->comment('mime类型')->d())
            ->addColumn(B::char('ext', 8)->ccAscii()->comment('文件后缀')->d())
            ->addColumn(B::unsignedInteger('size')->comment('文件大小')->d())
            ->addColumn(B::char('sha1', 40)->comment('SHA1')->d())
            ->addColumn(B::string('raw_file_name', 128)->comment('原始文件名')->d())
            ->addColumn(B::createTime()->d())
            ->addColumn(B::updateTime()->d())
            ->addIndex('index', B::index()->limit(48)->d())
            ->create();

        $exception_logs = $this->table('exception_logs', B::table()->comment('异常堆栈日志')->unsigned()->d());
        $exception_logs
            ->addColumn(B::createTime()->d())
            ->addColumn(B::string('request_url', 255)->comment('请求地址')->d())
            ->addColumn(B::string('request_route', 255)->comment('请求路由')->d())
            ->addColumn(B::string('request_method', 8)->comment('请求方法')->d())
            ->addColumn(B::string('request_ip', 46)->comment('请求IP')->d())
            ->addColumn(B::string('mode', 16)->comment('类型')->d())
            ->addColumn(B::text('request_info')->comment('请求信息')->d())
            ->addColumn(B::string('message', 2046)->comment('消息')->d())
            ->addColumn(B::text('trace_info')->comment('异常堆栈')->d())
            ->create();

        $permission = $this->table('permission', B::table()->comment('权限节点')->unsigned()->d());
        $permission
            ->addColumn(B::unsignedInteger('pid')->comment('父节点ID')->d())
            ->addColumn(B::genre()->comment('节点类型')->d())
            ->addColumn(B::string('nkey', 128)->ccAscii()->comment('节点命名key')->d())
            ->addColumn(B::string('hash', 8)->ccAscii()->comment('节点命名hash')->d())
            ->addColumn(B::string('lkey', 64)->ccAscii()->comment('节点逻辑key')->d())
            ->addColumn(B::unsignedTinyInteger('level')->comment('节点层级')->d())
            ->addColumn(B::string('action', 32)->ccAscii()->comment('节点方法')->d())
            ->addColumn(B::smallInteger('sort')->comment('节点排序')->default(255)->d())
            ->addColumn(B::string('class_name', 255)->ccAscii()->comment('节点类名')->d())
            ->addColumn(B::string('alias_name', 128)->comment('节点别名')->d())
            ->addColumn(B::string('description', 255)->comment('节点描述')->d())
            ->addColumn(B::integer('flags')->comment('选项标识')->d())
            ->addIndex('hash', ['unique' => true])
            ->addIndex(['pid', 'genre'], B::index()->name('index-1')->d())
            ->addIndex(['pid', 'sort'], B::index()->name('index-2')->d())
            ->addIndex('lkey', B::index()->unique()->limit(32)->d())
            ->addIndex('nkey', B::index()->unique()->limit(48)->d())
            ->create();
    }
}
