<?php
namespace DbMigrations;

use HZEX\Phinx\Schema;
use Phinx\Migration\AbstractMigration;
use Zxin\Phinx\Schema\Blueprint;

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
        Schema::cxt($this, function () {
            Schema::create('system', function (Blueprint $blueprint) {
                $blueprint->id = false;
                $blueprint->primaryKey = 'label';
                $blueprint->comment = '系统表';

                $blueprint->string('label', 48)->ccAscii()->comment('标签');
                $blueprint->string('value', 255)->comment('值');
                $blueprint->unique('label')->name('uk_label');
            });

            Schema::create('admin_role', function (Blueprint $blueprint) {
                $blueprint->comment = '系统角色';
                $blueprint->unsigned = true;

                $blueprint->genre()->comment('角色类型');
                $blueprint->status()->comment('角色状态');
                $blueprint->createTime();
                $blueprint->updateTime();
                $blueprint->deleteTime();
                $blueprint->string('name', 32)->comment('角色名称');
                $blueprint->string('description', 128)->comment('角色描述');
                $blueprint->json('ext')->comment('角色权限');
                $blueprint->lockVersion();
            });

            Schema::create('admin_user', function (Blueprint $blueprint) {
                $blueprint->comment = '系统用户';
                $blueprint->unsigned = true;

                $blueprint->genre()->comment('用户类型');
                $blueprint->status()->comment('用户状态');
                $blueprint->string('username', 32)->comment('用户账户');
                $blueprint->string('nickname', 32)->comment('用户昵称');
                $blueprint->string('password', 255)->comment('用户密码');
                $blueprint->string('email', 64)->nullable(true)->comment('用户邮箱');
                $blueprint->string('avatar', 96)->nullable(true)->comment('用户头像');
                $blueprint->unsignedInteger('role_id')->comment('角色ID');
                $blueprint->string('signup_ip', 46)->ccAscii()->comment('注册IP');
                $blueprint->createTime();
                $blueprint->updateTime();
                $blueprint->deleteTime();
                $blueprint->unsignedInteger('last_login_time')->comment('最后登录时间');
                $blueprint->string('last_login_ip', 46)->ccAscii()->comment('登录ip');
                $blueprint->string('remember', 16)->ccAscii()->comment('登录ip');
                $blueprint->lockVersion();
                $blueprint->unique(['username', 'delete_time'])->name('idx_username');
            });

            Schema::create('attachment', function (Blueprint $blueprint) {
                $blueprint->comment = '附件管理';
                $blueprint->unsigned = true;

                $blueprint->status();
                $blueprint->string('driver', 16)->ccAscii()->comment('文件驱动');
                $blueprint->string('index', 64)->ccAscii()->comment('文件索引');
                $blueprint->unsignedInteger('uid')->comment('操作用户');
                $blueprint->string('path', 255)->comment('文件路径');
                $blueprint->string('mime', 128)->ccAscii()->comment('mime类型');
                $blueprint->char('ext', 8)->ccAscii()->comment('文件后缀');
                $blueprint->unsignedInteger('size')->comment('文件大小');
                $blueprint->char('sha1', 40)->comment('SHA1');
                $blueprint->string('raw_file_name', 128)->comment('原始文件名');
                $blueprint->createTime();
                $blueprint->updateTime();
                $blueprint->index('index')->limit(48);
            });

            Schema::create('exception_logs', function (Blueprint $blueprint) {
                $blueprint->comment = '异常堆栈日志';
                $blueprint->unsigned = true;

                $blueprint->createTime();
                $blueprint->string('request_url', 255)->comment('请求地址');
                $blueprint->string('request_route', 255)->comment('请求路由');
                $blueprint->string('request_method', 8)->comment('请求方法');
                $blueprint->string('request_ip', 46)->comment('请求IP');
                $blueprint->string('mode', 16)->comment('类型');
                $blueprint->text('request_info')->comment('请求信息');
                $blueprint->string('message', 2048)->comment('消息');
                $blueprint->text('trace_info')->comment('异常堆栈');
            });
        });
    }
}
