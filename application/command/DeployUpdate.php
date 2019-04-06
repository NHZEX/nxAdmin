<?php
/**
 * Created by PhpStorm.
 * User: NHZEXG
 * Date: 2019/3/23
 * Time: 11:59
 */
declare(strict_types=1);

namespace app\command;

use basis\Util;
use think\console\Command;
use think\console\Input;
use think\console\input\Option;
use think\console\Output;
use think\facade\App;

class DeployUpdate extends Command
{
    public function configure()
    {
        $this
            ->setName('deploy:update')
            ->addOption('mode', 'm', Option::VALUE_OPTIONAL, '模式选择 [git]', 'git')
            ->addOption('yes', 'y', Option::VALUE_NONE, '更新确认')
            ->setDescription('执行系统更新 [TEST]');
    }

    /**
     * @param Input  $input
     * @param Output $output
     * @return int
     */
    public function execute(Input $input, Output $output): int
    {
        $output->info('whoami: ' . Util::whoami());
        $mode = $input->getOption('mode');

        switch ($mode) {
            case 'git':
                $result = $this->gitUpdate();
                break;
            default:
                $this->output->error("Invalid mode: {$mode}");
                $result = 1;
        }
        return $result;
    }

    public function gitUpdate(): int
    {
        $yes = (bool) $this->input->getOption('yes');

        $git_dir = App::getRootPath() . '.git';
        if (!is_dir($git_dir)) {
            $this->output->error("{$git_dir} does not exist");
            return 1;
        }

        $currBranchesName = trim(shell_exec('git rev-parse --abbrev-ref --symbolic-full-name HEAD'));
        $currBranchesUpstream = trim(shell_exec('git rev-parse --abbrev-ref --symbolic-full-name @{u}'));
        $this->output->info("curr branches name: {$currBranchesName}");
        $this->output->info("curr branches upstream: {$currBranchesUpstream}");

        $commands = [
            'echo $PWD',
            'git status',
            'git fetch',
            $yes ? "git pull --ff-only" : null,
        ];

        foreach ($commands as $command) {
            if (empty($command)) {
                continue;
            }
            $this->output->warning("exec: {$command}");
            // Run it
            $opts = [];
            exec($command, $opts, $return);
            // Output
            foreach ($opts as $opt) {
                $this->output->writeln($opt);
            }
            if (0 !== $return) {
                $this->output->error("exec fail: {$command}");
                return 1;
            }
        }
        return 0;
    }
}
