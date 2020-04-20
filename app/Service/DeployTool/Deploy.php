<?php

namespace app\Service\DeployTool;

use app\Service\DeployTool\Struct\EnvStruct;
use Exception;
use HZEX\Util;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;

class Deploy extends Command
{
    /** @var EnvStruct */
    protected $env;

    /**
     * @var bool 尝试执行
     */
    protected $dryRun = false;
    /**
     * @var bool
     */
    protected $devMode = false;
    /**
     * @var string
     */
    protected $envFilePath;
    /**
     * @var bool
     */
    protected $envFileExist;
    /**
     * @var string
     */
    protected $verbosity;
    /**
     * @var array
     */
    protected $config;

    /**
     * @var int
     */
    protected $code;

    public function configure()
    {
        $this
            ->setName('dep')
            ->setDescription('部署')
            ->addArgument('action', Argument::OPTIONAL, 'init, env, updata, migrate, user', '')
            ->addArgument('option', Argument::IS_ARRAY, 'action option ...', [])
            ->addOption('force', 'f', Option::VALUE_NONE, '强制覆盖')
            ->addOption('dev', 'd', Option::VALUE_NONE, '添加开发模式预设')
            ->addOption('run-user', null, Option::VALUE_OPTIONAL, '运行用户')
            ->addOption('add-username', null, Option::VALUE_OPTIONAL, '初始化用户名')
            ->addOption('add-password', null, Option::VALUE_OPTIONAL, '初始化用户名')
            ->addOption('max-retry', null, Option::VALUE_OPTIONAL, '最大重试次数', 10)
            ->addOption('dry-run', null, Option::VALUE_NONE, '尝试执行');
    }

    /**
     * 命令行入口
     * @param Input  $input
     * @param Output $output
     * @return int|void|null
     * @throws Exception
     */
    protected function execute(Input $input, Output $output)
    {
        $action = $input->getArgument('action');
        $option = $input->getArgument('option');

        $this->verbosity = str_repeat('v', $output->getVerbosity() - $output::VERBOSITY_NORMAL);

        $this->dryRun = (bool) $input->getOption('dry-run');
        $this->devMode = (bool) $input->getOption('dev');

        $this->envFilePath = $this->app->getRootPath() . '.env';
        $this->envFileExist = file_exists($this->envFilePath) && filesize($this->envFilePath) > 0;
        $this->config = $this->app->config->get('deploy');

        // 载入当前配置
        $this->env = EnvStruct::read();

        // 显示当前用户
        $this->env->TASK_USER = $input->getOption('run-user') ?: $this->env->TASK_USER;
        $output->info('当前用户：' . Util::whoami() . "({$this->env->TASK_USER})");
        $this->env->TASK_USER = $this->env->TASK_USER ?? Util::whoami();

        // 可用指令列表
        $actionList = [
            'auto' => '自动',
            'help' => '帮助',
            'env' => '常量管理',
            'user' => '用户管理',
            'update' => '更新管理'
        ];

        switch ($this->autoAction($action, $actionList)) {
            case 'auto':
                $this->auto($input, $output);
                break;
            case 'help':
                $this->showActionList($actionList);
                break;
            case 'env':
                $env = new EnvManage($this, $this->env);
                $env($input, $output, $option);
                break;
            case 'user':
                $user = new UserManage($this, $this->env);
                $user($input, $output, $option);
                break;
            case 'update':
                $update = new UpdateManage($this, $this->env);
                $update($input, $output, $option);
                break;
            default:
                if ($this->envFileExist) {
                    // 自动更新数据
                    $features = new UpdateManage($this, $this->env);
                    if (false === $features($input, $output, [$features->getDefaultAction()])) {
                        // 显示命令列表
                        $this->showActionList($actionList);
                    }
                } else {
                    $this->auto($input, $output);
                }
        }

        return $this->code;
    }

    /**
     * @param $input
     * @param $output
     * @throws Exception
     */
    public function auto($input, $output)
    {
        foreach ([EnvManage::class, UpdateManage::class, UserManage::class] as $class) {
            /** @var FeaturesManage $features */
            $features = new $class($this, $this->env);
            $features($input, $output, [$features->getDefaultAction()]);
        }
    }

    /**
     * @return bool
     */
    public function isDryRun(): bool
    {
        return $this->dryRun;
    }

    /**
     * @return bool
     */
    public function isDevMode(): bool
    {
        return $this->devMode;
    }

    /**
     * @return string
     */
    public function getVerbosity(): string
    {
        return $this->verbosity;
    }

    /**
     * @return string
     */
    public function getEnvFilePath(): string
    {
        return $this->envFilePath;
    }

    /**
     * @return bool
     */
    public function isEnvExist(): bool
    {
        return $this->envFileExist;
    }

    /**
     * @param bool $envFileExist
     */
    public function setEnvExist(bool $envFileExist): void
    {
        $this->envFileExist = $envFileExist;
    }

    /**
     * @param string|null $name
     * @return array
     */
    public function getConfig(?string $name = null)
    {
        if (null === $name) {
            return $this->config;
        }
        return $this->config[$name] ?? null;
    }

    /**
     * @param int $code
     */
    public function setCode(int $code): void
    {
        $this->code = $code;
    }

    /**
     * @param string|null $runAction
     * @param array       $actionList
     * @return bool|mixed|string|null
     */
    public function autoAction(?string $runAction, array $actionList)
    {
        if (empty($runAction)) {
            return null;
        }
        $name_hits = [];
        foreach ($actionList as $action => $description) {
            if (0 === strpos($action, $runAction)) {
                $name_hits[] = $action;
            }
        }
        if (count($name_hits) === 1) {
            $runAction = $name_hits[0];
        } elseif (count($name_hits) > 1) {
            $runAction = $this->output
                ->choice($this->input, "输入的指令（{$runAction}）可能是以下匹配: ", $name_hits, null);
        } else {
            $this->output->error("输入的指令不存在: {$runAction}");
            $runAction = null;
        }
        return $runAction;
    }

    /**
     * @param array $actionList
     */
    public function showActionList(array $actionList)
    {
        $maxLen = 0;
        foreach ($actionList as $action => $description) {
            $maxLen = max(strlen($action), $maxLen);
        }
        $maxLen += 8;

        $this->output->info('========指令列表========');
        foreach ($actionList as $action => $description) {
            $action = str_pad($action, $maxLen, ' ', STR_PAD_RIGHT);
            $this->output->info($action . $description);
        }
    }
}
