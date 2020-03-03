<?php

namespace DbMigrations;

use HZEX\Phinx\Schema;
use Phinx\Migration\AbstractMigration;

class Remove1 extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        Schema::cxt($this, function () {
            Schema::create('system_menu', function (Schema\Blueprint $blueprint) {
                $blueprint->table->drop()->save();
            });
            Schema::create('permission', function (Schema\Blueprint $blueprint) {
                $blueprint->table->drop()->save();
            });
        });
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
    }
}
