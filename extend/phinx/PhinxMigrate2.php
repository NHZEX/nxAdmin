<?php
/**
 * Created by PhpStorm.
 * User: NHZEXG
 * Date: 2019/2/21
 * Time: 14:53
 */

namespace phinx;

use Phinx\Config\Config as PhinxConfig;
use Phinx\Console\Command\Migrate as Migrate;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use think\Db;
use think\facade\App;

class PhinxMigrate2 extends Migrate
{
    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @throws \think\Exception
     */
    protected function loadConfig(InputInterface $input, OutputInterface $output)
    {
        $config = new PhinxConfig([
            'paths' => [
                'migrations' => App::getRootPath() . '/phinx/migrations',
                'seeds' => App::getRootPath() . '/phinx/seeds',
            ],
            'environments' => [
                'default_migration_table' => '_phinxlog',
                'default_database' => 'internal',
                'internal' => [
                    'connection' => Db::connect()->getConnection()->connect(),
                    'name' => Db::getConfig('database'),
                ],
            ],
            'version_order' => 'creation',
        ]);
        $this->setConfig($config);
    }
}
