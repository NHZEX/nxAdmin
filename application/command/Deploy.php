<?php
/**
 * Created by PhpStorm.
 * User: NHZEXG
 * Date: 2018/12/14
 * Time: 15:45
 */

namespace app\command;

use app\logic\Permission;
use app\logic\SystemMenu;
use app\model\AdminUser;
use app\server\Deploy as DeployServer;
use basis\Ini;
use facade\Redis;
use phinx\PhinxMigrate2;
use struct\EnvStruct;
use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\Console\Input\ArgvInput as SymfonyArgvInput;
use think\console\Command;
use think\console\Input;
use think\console\input\Option;
use think\console\Output;
use think\console\output\Ask;
use think\console\output\Question;
use think\Db;
use think\db\Connection;
use think\facade\App;

class Deploy extends Command
{
    const MYSQL_VER_LIMIT = '5.7.22-log';
    const REDIS_VER_LIMIT = '4.0.8';

    public function configure()
    {
        $this
            ->setName('deploy')
            ->setDescription('初始化部署')
            ->addOption('init', null, Option::VALUE_NONE, '强制初始化')
            ->addOption('force', 'f', Option::VALUE_NONE, '强制覆盖')
            ->addOption('dev', null, Option::VALUE_NONE, '开发模式预设')
            ->addOption('example', null, Option::VALUE_NONE, '生成范例文件')
            ->addOption('dry-run', null, Option::VALUE_NONE, '生成范例文件');
    }

    /**
     * @param Input  $input
     * @param Output $output
     * @return int|void|null
     * @throws \Matomo\Ini\IniReadingException
     * @throws \Matomo\Ini\IniWritingException
     * @throws \db\exception\ModelException
     * @throws \think\Exception
     */
    public function execute(Input $input, Output $output)
    {
        $dev = (bool)$input->getOption('dev');
        $forceInit = (bool)$input->getOption('init');
        $forceCover = (bool)$input->getOption('force');
        $dryRun = (bool)$input->getOption('dry-run');
        $envPath = App::getRootPath() . '.env';
        $existEnv = file_exists($envPath);

        if ((bool)$input->getOption('example')) {
            $output->writeln('生成ENV范例文件...');
            Ini::writerFile(App::getRootPath() . '.env.example', (new EnvStruct([]))->toArray());
            return;
        }

        // 载入当前配置
        $env = new EnvStruct($existEnv ? Ini::readerFile($envPath) : []);

        /**
         * 生成ENV配置文件
         * 1. ENV文件不存在
         * 2. ENV文件存在 且 强制覆盖
         * 3. 强制初始化
         */
        if (!$existEnv || ($existEnv && $forceCover && $forceInit)) {
            $output->writeln('生成部署设置...');
            if (!isset($env[DeployServer::ITEM_NAME])) {
                $env[DeployServer::ITEM_NAME] = DeployServer::init();
            }

            $output->writeln('配置Env设置...');
            $this->setEnv($env);

            // 开发模式预设
            if ($dev) {
                $env->app['debug'] = 1;
                $env->app['trace'] = 1;
                $env->app['tpl_cache'] = 0;
                $env->database['debug'] = 1;
            }

            if ($dryRun) {
                $content = Ini::writer($env->toArray(), Ini::HEADER_DATE);
                $output->writeln($content);
            } else {
                Ini::writerFile($envPath, $env->toArray(), Ini::HEADER_DATE);
            }
        }

        // 执行更新操作
        $output->writeln('启动更新作业...');
        $this->execUpdate($env, $dryRun);

        /**
         * 配置系统功能
         * ENV文件不存在 或 强制初始化
         */
        if (!$existEnv || $forceInit) {
            $output->writeln('配置系统功能...');

            $this->initAdminUser($env, $dryRun);
        }


        $output->writeln('所有操作都完成');
    }

    /**
     * @param EnvStruct $env
     * @param bool      $dryRun
     * @throws \think\exception\PDOException
     * @throws \Exception
     */
    protected function execUpdate(EnvStruct $env, bool $dryRun)
    {
        $output = $this->output;
        $verbosity = str_repeat('v', $output->getVerbosity() - $output::VERBOSITY_NORMAL);
        $verbosity = empty($verbosity) ? null : "-{$verbosity}";

        //构建数据库链接参数
        $database_config = array_merge(\think\facade\Config::pull('database'), $env->database);
        Db::init($database_config);

        // 执行数据迁移
        $output->writeln('================执行PHINX迁移================');
        $phinx = new SymfonyApplication();
        $phinx->add(new PhinxMigrate2());
        $argvInput = new SymfonyArgvInput(['.', 'migrate', $verbosity, $dryRun ? '--dry-run' : null]);
        $phinx->setAutoExit(false);
        $exitCode = $phinx->run($argvInput);
        if ($exitCode !== 0) {
            throw new \Exception("数据迁移发生异常中止\n");
        }
        $output->writeln('================执行PHINX完成================');

        // 更新权限节点
        $output->writeln('更新权限节点...');
        Permission::importNodes($dryRun);
        $output->writeln('更新菜单节点...');
        SystemMenu::import($dryRun);
    }

    protected function setEnv(EnvStruct $env)
    {
        $output = $this->output;

        /** @var \Exception $error */
        $error = null;

        while (true) {
            if (null !== $error) {
                $output->warning("提供的Mysql配置不正确：{$error->getMessage()}");
            }
            try {
                $this->inputMysqlConfig($env);
                break;
            } catch (\Exception $error) {
            }
        }
        while (true) {
            if (null !== $error) {
                $output->warning("提供的Redis配置不正确：{$error->getMessage()}");
            }
            try {
                $this->inputRedisConfig($env);
                break;
            } catch (\Exception $error) {
            }
        }
    }

    /**
     * @param EnvStruct $env
     * @param bool      $dryRun
     * @throws \db\exception\ModelException
     * @throws \think\Exception
     */
    protected function initAdminUser(EnvStruct $env, bool $dryRun)
    {
        $input = $this->input;
        $output = $this->output;

        $output->writeln('> 添加超级管理员');

        $question = new Question("输入管理员用户名\t\t", 'admin_' . get_rand_str(8));
        $question->setValidator(function ($value) {
            if (strlen($value) < 6) {
                throw new \Exception('用户名长度必须大于等于6位');
            }
            return $value;
        });
        $question->setMaxAttempts(3);
        $admin_username = $this->askQuestion($input, $output, $question);

        $question = new Question("输入管理员密码(隐藏)\t\t", get_rand_str(16));
        $question->setHidden(true);
        $question->setValidator(function ($value) {
            if (strlen($value) < 8) {
                throw new \Exception('密码长度必须大于等于8位');
            }
            return $value;
        });
        $question->setMaxAttempts(3);
        $admin_password = $this->askQuestion($input, $output, $question);

        $question = new Question("重新输入密码(隐藏)\t\t", str_repeat('*', strlen($admin_password)));
        $question->setHidden(true);
        $question->setValidator(function ($value) use ($admin_password) {
            if ($admin_password !== $value) {
                throw new \Exception('两次输入密码不一致');
            }
            return $value;
        });
        $question->setMaxAttempts(3);
        $this->askQuestion($input, $output, $question);
        $au = new AdminUser();
        $database_config = array_merge($au->getConfig(), $env->database);
        $au->setConnection(Connection::instance($database_config));
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
        };

        $output->writeln('> 用户创建成功');
    }

    /**
     * 输入数据库配置
     * @param EnvStruct $env
     * @throws \think\Exception
     * @throws \Exception
     */
    protected function inputMysqlConfig(EnvStruct $env)
    {
        $input = $this->input;
        $output = $this->output;

        $output->writeln('配置数据库');
        $default = "{$env->database['hostname']}:{$env->database['hostport']}";
        $question = new Question("地址\t", $default);
        $question->setValidator(function ($value) use ($env) {
            if (false === strpos($value, ':')) {
                $value .= ":{$env->database['hostport']}";
            }
            return $value;
        });
        $question->setMaxAttempts(3);
        $db_host = $this->askQuestion($input, $output, $question);
        [$env->database['hostname'], $env->database['hostport']] = explode(':', $db_host);

        $question = new Question("库名\t", $env->database['database']);
        $question->setValidator(function ($value) {
            if (empty($value)) {
                throw new \Exception('库名为空');
            }
            return $value;
        });
        $question->setMaxAttempts(3);
        $env->database['database'] = $this->askQuestion($input, $output, $question);

        $question = new Question("用户名\t", $env->database['username']);
        $question->setValidator(function ($value) {
            if (empty($value)) {
                throw new \Exception('用户名为空');
            }
            return $value;
        });
        $question->setMaxAttempts(3);
        $env->database['username'] = $this->askQuestion($input, $output, $question);

        $question = new Question("密码\t", $env->database['password']);
        $question->setValidator(function ($value) {
            if (empty($value)) {
                throw new \Exception('密码为空');
            }
            return $value;
        });
        $question->setMaxAttempts(3);
        $env->database['password'] = $this->askQuestion($input, $output, $question);

        $database_config = array_merge(\think\facade\Config::pull('database'), $env['database']);


        $mysql_ver = query_mysql_version($database_config);

        if (version_compare($mysql_ver, self::MYSQL_VER_LIMIT, '<')) {
            throw new \Exception("当前连接Mysql版本：{$mysql_ver}，最小限制版本：" . self::MYSQL_VER_LIMIT);
        }
        $output->writeln("当前连接Mysql版本：{$mysql_ver}");

        if (!query_mysql_exist_database($env->database['database'], $database_config)) {
            throw new \Exception("当前连接Mysql不存在库：{$env->database['database']}");
        }
    }

    /**
     * 输入Redis配置
     * @param EnvStruct $env
     * @throws \Exception
     */
    protected function inputRedisConfig(EnvStruct $env)
    {
        $input = $this->input;
        $output = $this->output;

        $output->writeln('配置Redis');
        $default = "{$env->redis['host']}:{$env->redis['port']}";
        $question = new Question("地址\t", $default);
        $question->setValidator(function ($value) use ($env) {
            if (false === strpos($value, ':')) {
                $value .= ":{$env->redis['port']}";
            }
            return $value;
        });
        $question->setMaxAttempts(3);
        $db_host = $this->askQuestion($input, $output, $question);
        [$env->redis['host'], $env->redis['port']] = explode(':', $db_host);

        $question = new Question("密码\t", $env->redis['password']);
        $question->setMaxAttempts(3);
        $env->redis['password'] = $this->askQuestion($input, $output, $question);

        $question = new Question("库名\t", $env->redis['select']);
        $question->setValidator(function ($value) {
            if (!is_numeric($value)) {
                throw new \Exception('库名无效');
            }
            return $value;
        });
        $question->setMaxAttempts(3);
        $env->redis['select'] = $this->askQuestion($input, $output, $question);

        Redis::setConfig($env->redis, true);
        if (Redis::getSelf()->ping() !== '+PONG') {
            throw new \Exception('Redis测试失败');
        }

        $redis_version = Redis::getSelf()->getServerVersion();
        if (version_compare($redis_version, self::REDIS_VER_LIMIT, '<')) {
            $errmsg = "当前连接Mysql版本：{$redis_version}，最小限制版本：" . self::REDIS_VER_LIMIT;
            throw new \Exception($errmsg);
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
