<?php
declare(strict_types=1);

namespace app\Service\DeployTool;

use app\Logic\Permission;
use app\Logic\SystemMenu;
use app\Model\System;
use Exception;
use Phinx\PhinxMigrate2;
use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\Console\Input\ArgvInput as SymfonyArgvInput;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use think\console\Output;

class UpdateManage extends FeaturesManage
{
    /**
     * 指令列表
     * @return array
     */
    public function getActionList(): array
    {
        return [
            'auto' => '自动完成',
            'migrate' => 'Db迁移',
            'data' => '数据更新',
        ];
    }

    /**
     * 默认指令
     * @return string
     */
    public function getDefaultAction(): string
    {
        return 'auto';
    }

    /**
     * @param Output $output
     * @return bool
     * @throws Exception
     */
    public function actionAuto(Output $output): bool
    {
        if ($this->actionMigrate($output) && $this->actionData($output)) {
            return true;
        }
        $this->deploy->setCode(1);
        return false;
    }

    /**
     * @param Output $output
     * @return bool
     * @throws Exception
     */
    public function actionMigrate(Output $output): bool
    {
        // TODO 环境检查
        if (false === $this->deploy->isEnvExist()) {
            $output->writeln('> 运行环境不正常');
            return false;
        }

        /** @noinspection PhpIncludeInspection */
        $config = require $this->app->getRootPath() . 'phinx.php';
        $verbosity = empty($this->deploy->getVerbosity()) ? null : "-{$this->deploy->getVerbosity()}";

        $output->writeln('> 执行数据迁移...');

        // 计算迁移版本Hash
        $config['paths']['migrations'];
        $finder = new Finder();
        $finder->files()->name('*.php')->in($config['paths']['migrations']);
        $migrationHash = '';
        /** @var SplFileInfo $file */
        foreach ($finder as $file) {
            $migrationHash .= $file->getRealPath() . md5_file($file->getRealPath(), true);
        }
        $migrationHash = md5($migrationHash);

        // 判断是否需要更新
        if (System::isAvailable() && $migrationHash === System::getLabel('dep_data_migration_ver')) {
            $output->writeln('  数据迁移: <comment>数据无需更新</comment>');
            return true;
        }

        // 执行数据迁移
        $phinx = new SymfonyApplication();
        $phinx->add(new PhinxMigrate2());
        $argv = ['.', 'migrate', $verbosity, $this->deploy->isDryRun() ? '--dry-run' : null];
        $argv = array_filter($argv);
        $argvInput = new SymfonyArgvInput($argv);
        $phinx->setAutoExit(false);
        $exitCode = $phinx->run($argvInput);
        if ($exitCode !== 0) {
            $output->writeln('  数据迁移: <error>数据迁移异常</error>');
            return false;
        }

        // 保存新的数据版本Hash
        System::setLabel('dep_data_migration_ver', $migrationHash);
        $output->writeln('  数据迁移: <info>数据迁移成功</info>');
        return true;
    }

    /**
     * @param Output $output
     * @return bool
     * @throws Exception
     */
    public function actionData(Output $output): bool
    {
        // TODO 环境检查
        if (false === $this->deploy->isEnvExist()) {
            $output->writeln('> 运行环境不正常');
            return false;
        }

        $result1 = $this->updateNodes($output);
        $result2 = $this->updateMenu($output);

        return $result1 && $result2;
    }

    /**
     * 更新权限节点
     * @param Output $output
     * @return bool
     * @throws Exception
     */
    protected function updateNodes(Output $output): bool
    {
        $output->writeln('> 更新权限节点...');
        $result = Permission::importNodes($this->deploy->isDryRun(), $message);
        $output->writeln('  权限数据: ' . $message);
        return $result;
    }

    /**
     * 更新菜单节点
     * @param Output $output
     * @return bool
     * @throws Exception
     */
    protected function updateMenu(Output $output): bool
    {
        $output->writeln('> 更新菜单节点...');
        // 虚拟当前请求
        $this->app->request->setSubDomain('/');
        $result = SystemMenu::import($this->deploy->isDryRun(), $message);
        $output->writeln('  菜单数据: ' . $message);
        return $result;
    }
}