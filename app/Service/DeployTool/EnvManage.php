<?php
declare(strict_types=1);

namespace app\Service\DeployTool;

use app\Facade\Redis;
use app\Server\DeployInfo;
use app\Struct\EnvStruct;
use Basis\Ini;
use Closure;
use Exception;
use think\console\Input;
use think\console\Output;
use think\console\output\Question;
use think\db\exception\BindParamException;
use think\db\exception\PDOException;

class EnvManage extends FeaturesManage
{
    const MYSQL_VER_LIMIT = '5.7.22-log';
    const REDIS_VER_LIMIT = '4.0.8';

    public function getActionList(): array
    {
        return [
            'init' => '初始化ENV',
            'example' => '生成ENV范例',
        ];
    }

    /**
     * 生成范例文件
     * @param Output $output
     */
    protected function actionExample(Output $output)
    {
        $output->writeln('生成ENV范例文件...');
        Ini::writerFile(
            $this->app->getRootPath() . '.env.example',
            (new EnvStruct([]))->toArray(),
            Ini::HEADER_DATE
        );
    }

    /**
     * 初始化ENV文件
     * @param Input  $input
     * @param Output $output
     * @throws Exception
     */
    protected function actionInit(Input $input, Output $output)
    {
        $forceCover = (bool) $input->getOption('force');

        /**
         * 生成ENV配置文件
         * 1. ENV文件不存在
         * 2. ENV文件存在 且 强制覆盖
         */
        if (!$this->deploy->isEnvExist() || ($this->deploy->isEnvExist() && $forceCover)) {
            $output->writeln('生成部署设置...');
            foreach (DeployInfo::init() as $key => $value) {
                $this->env[$key] = $value;
            }

            $output->writeln('配置Env设置...');
            $this->configEnv($this->env);

            // 开发模式预设
            if ($this->deploy->isDevMode()) {
                $this->env->APP_DEBUG = 1;
                $this->env->APP_TRACE = 1;
                $this->env->APP_TPL_CACHE = 0;
                $this->env->DATABASE_DEBUG = 1;
            } else {
                unset($this->env->DEVELOP_SECURE_DOMAIN_NAME);
            }

            if ($this->deploy->isDryRun()) {
                $content = Ini::writer($this->env->toArray(), Ini::HEADER_DATE);
                $output->writeln($content);
            } else {
                Ini::writerFile($this->deploy->getEnvFilePath(), $this->env->toArray(), Ini::HEADER_DATE);
            }

            $this->deploy->setEnvExist(true);
        } else {
            $output->writeln('ENV 已经存在');
        }
    }

    /**
     * @param EnvStruct $env
     * @param bool      $interaction 交互
     * @throws Exception
     */
    protected function configEnv(EnvStruct $env, bool $interaction = true)
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
     * 输入数据库配置
     * @param EnvStruct $env
     * @param bool      $interaction 交互
     * @throws BindParamException
     * @throws PDOException
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
        // 测试数据库配置
        $this->testMysql($env->DATABASE_DATABASE);
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

        $this->testRedis($this->getRedisConfig($env));
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
     * @param string $database
     * @throws BindParamException
     * @throws PDOException
     * @throws Exception
     */
    protected function testMysql(string $database)
    {
        // 检查mysql版本
        $mysql_ver = query_mysql_version();

        if (version_compare($mysql_ver, self::MYSQL_VER_LIMIT, '<')) {
            throw new Exception("当前连接Mysql版本：{$mysql_ver}，最小限制版本：" . self::MYSQL_VER_LIMIT);
        }
        $this->output->writeln("当前连接Mysql版本：{$mysql_ver}");

        if (!query_mysql_exist_database($database)) {
            throw new Exception("当前连接Mysql不存在库：{$this->env->DATABASE_DATABASE}");
        }
    }

    /**
     * @param array $config
     * @throws Exception
     */
    protected function testRedis(array $config)
    {
        Redis::setConfig($config, true);
        if (Redis::instance()->ping() !== '+PONG') {
            throw new Exception('Redis测试失败');
        }

        $redis_version = Redis::instance()->getServerVersion();
        if (version_compare($redis_version, self::REDIS_VER_LIMIT, '<')) {
            $errmsg = "当前连接Redis版本：{$redis_version}，最小限制版本：" . self::REDIS_VER_LIMIT;
            throw new Exception($errmsg);
        }
        $this->output->writeln("当前配置Redis版本：{$redis_version}");
    }
}
