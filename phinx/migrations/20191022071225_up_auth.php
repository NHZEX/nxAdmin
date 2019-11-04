<?php

namespace DbMigrations;

use Phinx\Blueprint as B;
use Phinx\Migration\AbstractMigration;

class UpPermission extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->table('permission')->drop();
        $admin_role = $this->table('permission', B::table()->comment('系统权限')->unsigned()->d());
        $admin_role
            ->addColumn(B::unsignedInteger('pid')->comment('父关联')->d())
            ->addColumn(B::string('name', 128)->ccAscii()->comment('权限名称')->d())
            ->addColumn(B::string('control', 4096)->ccAscii()->comment('权限分配')->d())
            ->create();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
