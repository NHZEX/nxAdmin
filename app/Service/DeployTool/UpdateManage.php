<?php
declare(strict_types=1);

namespace app\Service\DeployTool;

use app\Logic\Permission;
use app\Logic\SystemMenu;
use Exception;
use Phinx\PhinxMigrate2;
use ReflectionClass;
use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\Console\Input\ArgvInput as SymfonyArgvInput;
use think\console\Output;

class UpdateManage extends FeaturesManage
{
    /**
     * @return array
     */
    public function getActionList(): array
    {
        return [
            'auto' => '自动',
            'migrate' => '迁移',
            'update' => '更新',
        ];
    }

    /**
     * @param Output $output
     * @throws \ReflectionException
     * @throws Exception
     */
    public function actionUpdate(Output $output)
    {
        // TODO 环境检查
        if (false === $this->deploy->isEnvExist()) {
            $output->writeln('> 运行环境不正常');
            return;
        }

        $verbosity = empty($this->verbosity) ? null : "-{$this->verbosity}";

        // 执行数据迁移
        $output->writeln('================执行PHINX迁移================');
        $phinx = new SymfonyApplication();
        $phinx->add(new PhinxMigrate2());
        $argv = ['.', 'migrate', $verbosity, $this->deploy->isDryRun() ? '--dry-run' : null];
        $argv = array_filter($argv);
        $argvInput = new SymfonyArgvInput($argv);
        $phinx->setAutoExit(false);
        $exitCode = $phinx->run($argvInput);
        if ($exitCode !== 0) {
            throw new Exception("数据迁移发生异常中止\n");
        }
        $output->writeln('================执行PHINX完成================');

        // 更新权限节点
        $output->writeln('更新权限节点...');
        Permission::importNodes($this->deploy->isDryRun());
        $output->writeln('更新菜单节点...');
        $this->app->request->setSubDomain('/');
        $ref = new ReflectionClass($this->app->route);
        $p = $ref->getProperty('request');
        $p->setAccessible(true);
        $p->setValue($this->app->route, $this->app->request);
        SystemMenu::import($this->deploy->isDryRun());
    }
}
