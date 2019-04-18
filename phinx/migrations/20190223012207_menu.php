<?php
namespace DbMigrations;

use Phinx\Blueprint as B;
use Phinx\Migration\AbstractMigration;

class Menu extends AbstractMigration
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
        $permission = $this->table('system_menu', B::table()->comment('系统菜单')->unsigned()->d());
        $permission
            ->addColumn(B::unsignedInteger('pid')->comment('父关联')->d())
            ->addColumn(B::smallInteger('sort')->comment('菜单排序')->d())
            ->addColumn(B::status()->comment('菜单状态')->d())
            ->addColumn(B::string('node', 8)->ccAscii()->comment('关联节点')->d())
            ->addColumn(B::string('title', 64)->comment('菜单标题')->d())
            ->addColumn(B::string('icon', 64)->comment('菜单图标')->d())
            ->addColumn(B::string('url', 256)->comment('菜单地址')->d())
            ->addColumn(B::lockVersion()->d())
            ->addColumn(B::createTime()->d())
            ->addColumn(B::updateTime()->d())
            ->addColumn(B::deleteTime()->d())
            ->create();
    }
}
