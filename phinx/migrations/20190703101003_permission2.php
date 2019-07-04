<?php

namespace DbMigrations;

use Phinx\Blueprint as B;
use Phinx\Migration\AbstractMigration;

class Permission2 extends AbstractMigration
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
     * rollback the migration.
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {
        $admin_role = $this->table('permission_rule', B::table()->comment('权限规则')->unsigned()->d());
        $admin_role
            ->addColumn(B::unsignedInteger('pid')->comment('父节点')->d())
            ->addColumn(B::genre()->comment('规则类型')->d())
            ->addColumn(B::status()->comment('规则状态')->d())
            ->addColumn(B::createTime()->d())
            ->addColumn(B::updateTime()->d())
            ->addColumn(B::deleteTime()->d())
            ->addColumn(B::string('name', 32)->comment('规则名称')->d())
            ->addColumn(B::string('description', 128)->comment('规则描述')->d())
            ->addColumn(B::json('nodes')->comment('权限节点')->d())
            ->addColumn(B::lockVersion()->d())
            ->create();
    }
}
