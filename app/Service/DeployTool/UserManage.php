<?php
declare(strict_types=1);

namespace app\Service\DeployTool;

use app\Model\AdminUser;
use Exception;
use think\console\Input;
use think\console\Output;
use think\console\output\Question;
use think\console\Table;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;

class UserManage extends FeaturesManage
{
    /**
     * 指令列表
     * @return array
     */
    public function getActionList(): array
    {
        return [
            'auto' => '自动',
            'list' => '列出超级用户',
            'add' => '添加超级用户',
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
     * @param Input  $input
     * @param Output $output
     * @return bool
     * @throws Exception
     */
    public function actionAuto(Input $input, Output $output)
    {
        // TODO 环境检查
        if (false === $this->deploy->isEnvExist()) {
            $output->writeln('> 运行环境不正常');
            return false;
        }

        if ($this->deploy->isDryRun()) {
            $output->writeln('> 跳过用户创建');
            return true;
        }

        $existUser = AdminUser::where('genre', '=', AdminUser::GENRE_SUPER_ADMIN)
                ->where('status', '=', AdminUser::STATUS_NORMAL)
                ->count() > 0;

        if (false === $existUser) {
            return $this->actionAdd($input, $output);
        } else {
            $output->writeln('> 可用的超级用户以存在');
        }

        return true;
    }

    /**
     * @param Output $output
     * @return bool
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function actionList(Output $output)
    {
        // TODO 环境检查
        if (false === $this->deploy->isEnvExist()) {
            $output->writeln('> 运行环境不正常');
            return false;
        }

        $users = (new AdminUser())->hidden(['password', 'delete_time'])
            ->where('genre', '=', AdminUser::GENRE_SUPER_ADMIN)
            ->select();
        if (!$users->isEmpty()) {
            $users->load(['beRoleName']);
            $users->append(['status_desc', 'genre_desc']);
        }

        $table = new Table();
        $table->setHeader(['id', 'genre', 'name', 'nick', 'status']);

        foreach ($users as $user) {
            $table->addRow([$user->id, $user->genre_desc, $user->username, $user->nickname, $user->status_desc]);
        }

        $output->write($table->render());

        return true;
    }

    /**
     * @param Input  $input
     * @param Output $output
     * @return bool
     * @throws Exception
     */
    public function actionAdd(Input $input, Output $output)
    {
        // TODO 环境检查
        if (false === $this->deploy->isEnvExist()) {
            $output->writeln('> 运行环境不正常');
            return false;
        }

        $noInteraction = (bool) $input->getOption('no-interaction');

        $output->writeln('> 添加超级管理员');

        $admin_username = $this->app->env->get('INIT_SADMIN_USERNAME', $input->getOption('add-username'));
        $admin_password = $this->app->env->get('INIT_SADMIN_PASSWORD', $input->getOption('add-password'));

        if (empty($admin_username)) {
            $question = new Question("输入管理员用户名\t\t", 'admin_' . get_rand_str(8));
            $question->setValidator(function ($value) {
                if (strlen($value) < 6) {
                    throw new Exception('用户名长度必须大于等于6位');
                }
                return $value;
            });
            $question->setMaxAttempts(3);
            $admin_username = $this->askQuestion($input, $output, $question);
        } else {
            if (strlen($admin_username) < 6) {
                throw new Exception('用户名长度必须大于等于6位');
            }
        }

        if (empty($admin_password)) {
            $question = new Question("输入管理员密码(隐藏)\t\t", get_rand_str(16));
            $question->setHidden(true);
            $question->setValidator(function ($value) {
                if (strlen($value) < 6) {
                    throw new Exception('密码长度必须大于等于6位');
                }
                return $value;
            });
            $question->setMaxAttempts(3);
            $admin_password = $this->askQuestion($input, $output, $question);

            $question = new Question("重新输入密码(隐藏)\t\t", str_repeat('*', strlen($admin_password)));
            $question->setHidden(true);
            $question->setValidator(function ($value) use ($admin_password) {
                if ($admin_password !== $value) {
                    throw new Exception('两次输入密码不一致');
                }
                return $value;
            });
            $question->setMaxAttempts(3);
            $this->askQuestion($input, $output, $question);
        } else {
            if (strlen($admin_password) < 6) {
                throw new Exception('密码长度必须大于等于6位');
            }
        }

        // 禁用权限控制
        defined('DISABLE_ACCESS_CONTROL') || define('DISABLE_ACCESS_CONTROL', true);

        // 创建新用户
        $au = new AdminUser();
        $au->genre = AdminUser::GENRE_SUPER_ADMIN;
        $au->username = $au->nickname = $admin_username;
        $au->password = $admin_password;
        $au->role_id = 0;
        if ($this->deploy->isDryRun()) {
            $creatde_sql = $au->fetchSql(true)->insert($au->getData());
            $output->writeln($creatde_sql);
        } else {
            $au->save();
            if ($output->isDebug()) {
                $output->writeln($au->getLastSql());
            }
        }

        $output->writeln('> 用户创建成功' . ($noInteraction ? '<comment>[回显]</comment>' : ''));
        if ($noInteraction) {
            $output->writeln("  > 用户账号: {$admin_username}");
            $output->writeln("  > 用户密码: {$admin_password}");
        }

        return true;
    }
}
