<?php

namespace DbMigrations;

use Phinx\Blueprint as B;
use Phinx\Migration\AbstractMigration;

class UpAuth extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->table('permission')->drop()->save();
        $admin_role = $this->table('permission', B::table()->comment('系统权限')->unsigned()->d());
        $admin_role
            ->addColumn(B::genre()->comment('节点类型')->d())
            ->addColumn(B::smallInteger('sort')->comment('节点排序')->d())
            ->addColumn(B::string('name', 128)->ccAscii()->comment('权限名称')->d())
            ->addColumn(B::string('pid', 128)->ccAscii()->comment('父关联')->d())
            ->addColumn(B::json('control')->nullable(true)->comment('授权内容')->d())
            ->addColumn(B::string('desc', 512)->comment('权限描述')->d())
            ->create();

        $system_menu = $this->table('system_menu');
        $system_menu
            ->changeColumn('node', B::string('node', 128)->ccAscii()->comment('关联权限')->d())
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
    }
}
