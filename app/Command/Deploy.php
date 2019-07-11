<?php
/**
 * Created by PhpStorm.
 * User: NHZEXG
 * Date: 2018/12/14
 * Time: 15:45
 */

namespace app\Command;

use app\Facade\Redis;
use app\Logic\Permission;
use app\Logic\SystemMenu;
use app\Model\AdminUser;
use app\Server\DeployInfo;
use app\Struct\EnvStruct;
use Basis\Ini;
use Closure;
use Exception;
use HZEX\Util;
use Phinx\PhinxMigrate2;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\Console\Input\ArgvInput as SymfonyArgvInput;
use think\App;
use think\console\Command;
use think\console\Input;
use think\console\input\Option;
use think\console\Output;
use think\console\output\Ask;
use think\console\output\Question;
use think\Env;
use think\Exception as ExceptionThink;

class Deploy extends Command
{
    const MYSQL_VER_LIMIT = '5.7.22-log';
    const REDIS_VER_LIMIT = '4.0.8';

    /** @var App */
    protected $app;
    /** @var Env */
    protected $env;

    public function configure()
    {
        $this
            ->setName('dep')
            ->setDescription('初始化部署')
            ->addOption('only-update', 'u', Option::VALUE_NONE, '只进行更新(禁用所有交互)')
            ->addOption('force', 'f', Option::VALUE_NONE, '强制覆盖')
            ->addOption('dev', 'd', Option::VALUE_NONE, '添加开发模式预设')
            ->addOption('run-user', null, Option::VALUE_OPTIONAL, '运行用户')
            ->addOption('init', null, Option::VALUE_NONE, '强制初始化')
            ->addOption('init-username', null, Option::VALUE_OPTIONAL, '初始化用户名')
            ->addOption('init-password', null, Option::VALUE_OPTIONAL, '初始化用户名')
            ->addOption('ci', null, Option::VALUE_NONE, '启用持续集成支持')
            ->addOption('no-migrate', 'm', Option::VALUE_NONE, '不执行迁移')
            ->addOption('example', null, Option::VALUE_NONE, '生成范例文件')
            ->addOption('dry-run', null, Option::VALUE_NONE, '尝试执行');
    }

    /**
     * @param Input  $input
     * @param Output $output
     * @return int
     * @throws Exception
     */
    public function execute(Input $input, Output $output): int
    {
        $this->app = App::getInstance();
        $this->env = $this->app->env;

        $update = (bool) $input->getOption('only-update');
        $dev = (bool) $input->getOption('dev');
        $forceInit = (bool) $input->getOption('init');
        $forceCover = (bool) $input->getOption('force');
        $notMigrate = (bool) $input->getOption('no-migrate');
        $dryRun = (bool) $input->getOption('dry-run');
        $ci = (bool) $input->getOption('ci');
        $envPath = $this->app->getRootPath() . '.env';
        $existEnv = file_exists($envPath) && filesize($envPath) > 0 && !empty(file_get_contents($envPath));

        if ((bool) $input->getOption('example')) {
            $output->writeln('生成ENV范例文件...');
            Ini::writerFile(
                $this->app->getRootPath() . '.env.example',
                (new EnvStruct([]))->toArray(),
                Ini::HEADER_DATE
            );
            return 0;
        }

        // 载入当前配置
        $env = EnvStruct::read();
        // 载入允许用户
        $env->TASK_USER = $input->getOption('run-user') ?: $env->TASK_USER;
        $output->info('当前用户：' . Util::whoami() . "({$env->TASK_USER})");
        $env->TASK_USER = $env->TASK_USER ?? Util::whoami();

        if ($update && !$existEnv) {
            $output->error('部署文件不存在，请先部署');
            return 1;
        }

        /**
         * 生成ENV配置文件
         * 1. ENV文件不存在
         * 2. ENV文件存在 且 强制覆盖
         */
        if (!$existEnv || (!$update && $existEnv && $forceCover)) {
            $output->writeln('生成部署设置...');
            foreach (DeployInfo::init() as $key => $value) {
                $env[$key] = $value;
            }

            $output->writeln('配置Env设置...');
            $this->setEnv($env, !$ci);

            // 开发模式预设
            if ($dev) {
                $env->APP_DEBUG = 1;
                $env->APP_TRACE = 1;
                $env->APP_TPL_CACHE = 0;
                $env->DATABASE_DEBUG = 1;
            } else {
                unset($env->DEVELOP_SECURE_DOMAIN_NAME);
            }

            if ($dryRun) {
                $content = Ini::writer($env->toArray(), Ini::HEADER_DATE);
                $output->writeln($content);
            } else {
                Ini::writerFile($envPath, $env->toArray(), Ini::HEADER_DATE);
            }
        }

        // 执行更新操作
        if (!$notMigrate) {
            $output->writeln('启动更新作业...');
            $this->execUpdate($dryRun);
        }

        /**
         * 配置系统功能
         * ENV文件不存在 或 强制初始化
         */
        if (!$update && (!$existEnv || $forceInit)) {
            $output->writeln('配置系统功能...');

            $this->initAdminUser($dryRun);
        }

        $output->writeln('所有操作都完成');
        return 0;
    }

    /**
     * @param bool $dryRun
     * @throws Exception
     * @throws ReflectionException
     */
    protected function execUpdate(bool $dryRun)
    {
        $output = $this->output;
        $verbosity = str_repeat('v', $output->getVerbosity() - $output::VERBOSITY_NORMAL);
        $verbosity = empty($verbosity) ? null : "-{$verbosity}";

        // 执行数据迁移
        $output->writeln('================执行PHINX迁移================');
        $phinx = new SymfonyApplication();
        $phinx->add(new PhinxMigrate2());
        $argv = ['.', 'migrate', $verbosity, $dryRun ? '--dry-run' : null];
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
        Permission::importNodes($dryRun);
        $output->writeln('更新菜单节点...');
        $this->app->request->setSubDomain('/');
        $ref = new ReflectionClass($this->app->route);
        $p = $ref->getProperty('request');
        $p->setAccessible(true);
        $p->setValue($this->app->route, $this->app->request);
        SystemMenu::import($dryRun);
    }

    /**
     * @param EnvStruct $env
     * @param bool      $interaction 交互
     * @throws Exception
     */
    protected function setEnv(EnvStruct $env, bool $interaction = true)
    {
        $this->checkInput(function () use ($env, $interaction) {
            $this->inputMysqlConfig($env, $interaction);
        }, '提供的Mysql配置不正确：%s', $interaction);

        $this->checkInput(function () use ($env, $interaction) {
            $this->inputRedisConfig($env, $interaction);
        }, '提供的Redis配置不正确：%s', $interaction);
    }

    /**
     * 校验输入是否正确
     * @param Closure $closure
     * @param string  $message
     * @param bool    $interaction 交互
     * @throws Exception
     */
    protected function checkInput(Closure $closure, string $message, bool $interaction = true)
    {
        /** @var Exception $error */
        $error = null;
        while (true) {
            if (null !== $error) {
                $this->output->warning(str_replace('%s', $error->getMessage(), $message));
            }
            try {
                $closure();
                break;
            } catch (Exception $error) {
                if ($interaction) {
                    // 防止死循环
                    usleep(500000);
                } else {
                    throw $error;
                }
            }
        }
    }

    /**
     * @param EnvStruct $env
     * @return array
     */
    private function getDbConfig(EnvStruct $env)
    {
        $config = $this->app->config->get('database');
        $config['connections']['main'] = array_merge($config['connections']['main'], [
            'hostname' => $env->DATABASE_HOSTNAME,
            'hostport' => (int) $env->DATABASE_HOSTPORT,
            'database' => $env->DATABASE_DATABASE,
            'username' => $env->DATABASE_USERNAME,
            'password' => $env->DATABASE_PASSWORD,
        ]);
        return $config;
    }

    /**
     * @param EnvStruct $env
     * @return array
     */
    private function getRedisConfig(EnvStruct $env)
    {
        return [
            'host' => $env->REDIS_HOST,
            'port' => $env->REDIS_PORT,
            'password' => $env->REDIS_PASSWORD,
            'select' => (int) $env->REDIS_SELECT,
            'timeout' => (int) $env->REDIS_TIMEOUT,
            'persistent' => (bool) $env->REDIS_PERSISTENT,
        ];
    }

    /**
     * @param bool $dryRun
     * @throws Exception
     */
    protected function initAdminUser(bool $dryRun)
    {
        $input = $this->input;
        $output = $this->output;

        $output->writeln('> 添加超级管理员');

        $admin_username = $input->getOption('init-username');
        $admin_password = $input->getOption('init-password');

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

        $au = new AdminUser();

        $au->setConnection($this->app->db->getConnection());
        $au->genre = AdminUser::GENRE_SUPER_ADMIN;
        $au->username = $au->nickname = $admin_username;
        $au->password = $admin_password;
        $au->role_id = 0;
        $au->turnOffAccessControl();
        if ($dryRun) {
            $creatde_sql = $au->fetchSql(true)->insert($au->getData(null));
            $output->writeln($creatde_sql);
        } else {
            $au->save();
        }

        $output->writeln('> 用户创建成功');
    }

    /**
     * 输入数据库配置
     * @param EnvStruct $env
     * @param bool      $interaction 交互
     * @throws ExceptionThink
     * @throws Exception
     */
    protected function inputMysqlConfig(EnvStruct $env, bool $interaction = true)
    {
        $input = $this->input;
        $output = $this->output;

        $output->writeln('配置数据库');

        if ($interaction) {
            $default = "{$env->DATABASE_HOSTNAME}:{$env->DATABASE_HOSTPORT}";
            $question = new Question("地址\t", $default);
            $question->setValidator(function ($value) use ($env) {
                if (false === strpos($value, ':')) {
                    $value .= ":{$env->DATABASE_HOSTPORT}";
                }
                return $value;
            });
            $question->setMaxAttempts(3);
            $db_host = $this->askQuestion($input, $output, $question);
            [$env->DATABASE_HOSTNAME, $env->DATABASE_HOSTPORT] = explode(':', $db_host);

            $question = new Question("库名\t", $env->DATABASE_DATABASE);
            $question->setValidator(function ($value) {
                if (empty($value)) {
                    throw new Exception('库名为空');
                }
                return $value;
            });
            $question->setMaxAttempts(3);
            $env->DATABASE_DATABASE = $this->askQuestion($input, $output, $question);

            $question = new Question("用户名\t", $env->DATABASE_USERNAME);
            $question->setValidator(function ($value) {
                if (empty($value)) {
                    throw new Exception('用户名为空');
                }
                return $value;
            });
            $question->setMaxAttempts(3);
            $env->DATABASE_USERNAME = $this->askQuestion($input, $output, $question);

            $question = new Question("密码\t", $env->DATABASE_PASSWORD);
            $question->setValidator(function ($value) {
                if (empty($value)) {
                    throw new Exception('密码为空');
                }
                return $value;
            });
            $question->setMaxAttempts(3);
            $env->DATABASE_PASSWORD = $this->askQuestion($input, $output, $question);
        }

        // 重设数据库配置
        $this->app->db->setConfig($this->getDbConfig($env));
        // 检查mysql版本
        $mysql_ver = query_mysql_version();

        if (version_compare($mysql_ver, self::MYSQL_VER_LIMIT, '<')) {
            throw new Exception("当前连接Mysql版本：{$mysql_ver}，最小限制版本：" . self::MYSQL_VER_LIMIT);
        }
        $output->writeln("当前连接Mysql版本：{$mysql_ver}");

        if (!query_mysql_exist_database($env->DATABASE_DATABASE)) {
            throw new Exception("当前连接Mysql不存在库：{$env->DATABASE_DATABASE}");
        }
    }

    /**
     * 输入Redis配置
     * @param EnvStruct $env
     * @param bool      $interaction 交互
     * @throws Exception
     */
    protected function inputRedisConfig(EnvStruct $env, bool $interaction = true)
    {
        $input = $this->input;
        $output = $this->output;

        $output->writeln('配置Redis');

        if ($interaction) {
            $default = "{$env->REDIS_HOST}:{$env->REDIS_PORT}";
            $question = new Question("地址\t", $default);
            $question->setValidator(function ($value) use ($env) {
                if (false === strpos($value, ':')) {
                    $value .= ":{$env->REDIS_PORT}";
                }
                return $value;
            });
            $question->setMaxAttempts(3);
            $db_host = $this->askQuestion($input, $output, $question);
            [$env->REDIS_HOST, $env->REDIS_PORT] = explode(':', $db_host);

            $question = new Question("密码\t", $env->REDIS_PASSWORD);
            $question->setMaxAttempts(3);
            $env->REDIS_PASSWORD = $this->askQuestion($input, $output, $question);

            $question = new Question("库名\t", $env->REDIS_SELECT);
            $question->setValidator(function ($value) {
                if (!is_numeric($value)) {
                    throw new Exception('库名无效');
                }
                return $value;
            });
            $question->setMaxAttempts(3);
            $env->REDIS_SELECT = $this->askQuestion($input, $output, $question);
        }

        Redis::setConfig($this->getRedisConfig($env), true);
        if (Redis::instance()->ping() !== '+PONG') {
            throw new Exception('Redis测试失败');
        }

        $redis_version = Redis::instance()->getServerVersion();
        if (version_compare($redis_version, self::REDIS_VER_LIMIT, '<')) {
            $errmsg = "当前连接Mysql版本：{$redis_version}，最小限制版本：" . self::REDIS_VER_LIMIT;
            throw new Exception($errmsg);
        }
        $output->writeln("当前配置Redis版本：{$redis_version}");
    }

    /**
     * 快捷应答
     * @param Input    $input
     * @param Output   $output
     * @param Question $question
     * @param bool     $isInteractive
     * @return bool|mixed|string
     */
    protected function askQuestion(Input $input, Output $output, Question $question, bool $isInteractive = false)
    {
        $ask = new Ask($input, $output, $question);
        $answer = $ask->run();

        if ($isInteractive && $input->isInteractive()) {
            $output->newLine();
        }

        return $answer;
    }
}
