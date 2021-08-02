<?php
declare(strict_types=1);

namespace DbMigrations;

use HZEX\Phinx\Schema;
use Phinx\Migration\AbstractMigration;
use Zxin\Phinx\Schema\Blueprint;

final class Activity extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change(): void
    {
        Schema::cxt($this, function () {
            Schema::create('activity_log', function (Blueprint $blueprint) {
                $blueprint->comment = '活动日志';

                $blueprint->unsignedInteger('user_id')->comment('用户ID');
                $blueprint->createTime();
                $blueprint->string('auth_name', 64)->asciiCharacter();
                $blueprint->string('target', 255)->asciiCharacter();
                $blueprint->string('method', 8)->asciiCharacter();
                $blueprint->string('url', 255);
                $blueprint->string('ip', 40)->asciiCharacter();
                $blueprint->unsignedSmallInteger('http_code');
                $blueprint->string('resp_code', 32)->asciiCharacter();
                $blueprint->text('resp_message');
                $blueprint->json('details')->nullable(true);
            });
        });
    }
}
