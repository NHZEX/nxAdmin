<?php
/** @noinspection PhpRedundantCatchClauseInspection */
declare(strict_types=1);

namespace app\Service\DeployTool;

use app\Service\DeployTool\Exception\ConfigInvalidException;
use app\Service\DeployTool\Exception\InputException;
use app\Service\DeployTool\Struct\EnvStruct;
use app\Service\Redis\Connections\PhpRedisConnection;
use Closure;
use Exception;
use PDOException;
use RedisException;
use think\console\Input;
use think\console\Output;

class EnvManage extends FeaturesManage
{
    const MYSQL_VER_LIMIT = '5.7.22-log';
    const REDIS_VER_LIMIT = '4.0.8';

    /**
     * 指令列表
     * @return array
     */
    public function getActionList(): array
    {
        return [
            'init' => '初始化ENV',
            'example' => '生成ENV范例',
        ];
    }

    /**
     * 默认指令
     * @return string
     */
    public function getDefaultAction(): string
    {
        return 'init';
    }

    /**
     * 生成范例文件
     * @param Output $output
     */
    protected function actionExample(Output $output)
    {
        $output->writeln('生成ENV范例文件...');
        EnvFormat::writerFile(
            $this->app->getRootPath() . '.env.example',
            (new EnvStruct([]))->toArray(),
            EnvFormat::HEADER_DATE
        );
    }

    /**
     * 初始化ENV文件
     * @param Input  $input
     * @param Output $output
     * @throws Exception
     */
    public function actionInit(Input $input, Output $output)
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
            $this->configEnv();

            // 开发模式预设
            if ($this->deploy->isDevMode()) {
                $this->env->APP_DEBUG = 1;
                $this->env->APP_TRACE = 1;
            } else {
                unset($this->env->DEVELOP_SECURE_DOMAIN_NAME);
            }

            if ($this->deploy->isDryRun()) {
                $content = EnvFormat::writer($this->env->toArray(), EnvFormat::HEADER_DATE);
                $output->writeln($content);
            } else {
                EnvFormat::writerFile($this->deploy->getEnvFilePath(), $this->env->toArray(), EnvFormat::HEADER_DATE);
            }

            $this->deploy->setEnvExist(true);
        } else {
            $output->writeln('> ENV 已经存在');
        }
    }

    /**
     * @throws Exception
     */
    protected function configEnv()
    {
        $this->checkInput(function () {
            $this->configDataBase();
        }, '提供的数据库配置不正确：%s');

        $this->checkInput(function () {
            $this->configRedis();
        }, '提供的Redis配置不正确：%s');

        $this->checkInput(function () {
            $this->configLog();
        }, '提供的日志配置不正确：%s');

        $this->checkInput(function () {
            $this->configCache();
        }, '提供的缓存配置不正确：%s');

        $this->checkInput(function () {
            $this->configSession();
        }, '提供的会话配置不正确：%s');
    }

    /**
     * 校验输入是否正确
     * @param Closure $closure
     * @param string  $template
     * @throws Exception
     */
    protected function checkInput(Closure $closure, string $template)
    {
        $count = (int) $this->input->getOption('max-retry');
        while (true) {
            try {
                $closure();
                break;
            } catch (InputException | ConfigInvalidException $error) {
                if ($count-- || ((bool) $this->input->getOption('no-interaction') && $count)) {
                    // 防止死循环
                    sleep(1);
                    // 打印错误信息
                    $message = str_replace('%s', $error->getMessage(), $template);
                    $this->output->writeln("<highlight>{$message}</highlight>");
                } else {
                    throw $error;
                }
            }
        }
    }

    /**
     * 输入数据库配置
     * @throws ConfigInvalidException
     * @throws InputException
     */
    protected function configDataBase()
    {
        $this->output->writeln('> 配置数据库');

        $configName = 'db';
        $dbPreset = $this->deploy->getConfig($configName);

        // 载入数据库配置
        foreach ($this->envExtract($configName, 3) as $data) {
            [$group, $name, $value] = $data;
            if (isset($dbPreset[$group]) && isset($dbPreset[$group]['form'][$name])) {
                $dbPreset[$group]['form'][$name][0] = $value;
            }
        }

        foreach ($dbPreset as $name => $info) {
            $this->output->writeln(" > 配置库: {$info['desc']}({$name})");
            $result = $this->showFormsInput($info['form']);
            // 测试配置
            switch ($info['type']) {
                case 'mysql':
                    $this->testMysql($name, $result);
                    break;
                default:
            }
            // 转换为常量
            $result = $this->toEnvFormat($configName, $name, $result);
            // 写到常量
            foreach ($result as $key => $value) {
                $this->env->$key = $value;
            }
        }
    }

    /**
     * 输入Redis配置
     * @throws ConfigInvalidException
     * @throws InputException
     */
    protected function configRedis()
    {
        $this->output->writeln('> 配置Redis');

        $configName = 'redis';
        $redisPreset = $this->deploy->getConfig($configName);

        // 载入Redis配置
        foreach ($this->envExtract($configName, 2) as $data) {
            [, $name, $value] = $data;
            $redisPreset[$name][0] = $value;
        }

        $result = $this->showFormsInput($redisPreset);
        // 测试配置
        $this->testRedis($result);
        // 转换为常量
        $result = $this->toEnvFormat($configName, null, $result);
        // 写到常量
        foreach ($result as $key => $value) {
            $this->env->$key = $value;
        }
    }

    /**
     * @throws ConfigInvalidException
     * @throws InputException
     */
    protected function configSession()
    {
        $this->output->writeln('> 配置Session');

        $cachePreset = $this->deploy->getConfig('session');

        // 载入Session配置
        foreach ($this->envExtract('SESSION', 2) as $data) {
            [, $name, $value] = $data;
            $cachePreset[$name][0] = $value;
        }

        $config = [];
        if ($this->output->confirm($this->input, '是否复用Redis配置', true)) {
            // 载入Redis配置
            foreach ($this->envExtract('REDIS', 2) as $data) {
                [, $name, $value] = $data;
                // 赋值继承值
                $config["redis_{$name}"] = $value;
                // 移除已经赋值的输入表项
                unset($cachePreset["redis_{$name}"]);
            }
        }

        $config = $config + $this->showFormsInput($cachePreset);

        // 测试配置
        $this->testRedis([
            'host' => $config['redis_host'],
            'port' => $config['redis_port'],
            'password' => $config['redis_password'],
            'select' => $config['redis_select'],
            'timeout' => $config['redis_timeout'],
            'persistent' => $config['redis_persistent'],
        ]);
        // 转换为常量
        $config = $this->toEnvFormat('SESSION', null, $config);
        // 写到常量
        foreach ($config as $key => $value) {
            $this->env->$key = $value;
        }
    }

    /**
     * 设置Cache
     * @throws ConfigInvalidException
     */
    protected function configCache()
    {
        // TODO 配置缓存
    }

    protected function configLog()
    {
        // TODO 配置日志
    }

    /**
     * 测试数据库连接
     * TODO 扩展更多连接类型支持
     * @param string $connections
     * @param array  $testConfig
     * @throws ConfigInvalidException
     */
    protected function testMysql(string $connections, array $testConfig)
    {
        $config = $this->app->config->get('database');
        $config['connections'][$connections] = array_merge($config['connections'][$connections], $testConfig);
        $this->app->config->set($config, 'database');
        $this->app->delete('db');

        // 检查mysql版本
        try {
            $mysql_ver = query_mysql_version($connections);
        } catch (PDOException $exception) {
            $address = empty($testConfig['dsn'])
                ? "{$testConfig['hostname']}:{$testConfig['hostport']}"
                : $testConfig['dsn'];
            $message = "数据库连接[$address]异常：{$exception->getMessage()}";
            throw new ConfigInvalidException($message, $exception->getCode(), $exception);
        }

        if (version_compare($mysql_ver, self::MYSQL_VER_LIMIT, '<')) {
            throw new ConfigInvalidException("当前连接Mysql版本：{$mysql_ver}，最小限制版本：" . self::MYSQL_VER_LIMIT);
        }
        $this->output->writeln("当前连接Mysql版本：{$mysql_ver}");

        if (!query_mysql_exist_database($testConfig['database'], $connections)) {
            throw new ConfigInvalidException("欲连接的库不存：{$testConfig['database']}");
        }
    }

    /**
     * @param array $config
     * @throws ConfigInvalidException
     */
    protected function testRedis(array $config)
    {
        $redis = new PhpRedisConnection($config);

        try {
            if (!$redis->ping()) {
                throw new ConfigInvalidException('Redis测试失败');
            }
        } catch (RedisException $exception) {
            $message = "Redis连接[{$config['host']}:{$config['port']}]异常：{$exception->getMessage()}";
            throw new ConfigInvalidException($message, $exception->getCode(), $exception);
        }

        $redis_version = $redis->getServerVersion();
        if (version_compare($redis_version, self::REDIS_VER_LIMIT, '<')) {
            $errmsg = "当前连接Redis版本：{$redis_version}，最小限制版本：" . self::REDIS_VER_LIMIT;
            throw new ConfigInvalidException($errmsg);
        }
        $this->output->writeln("当前配置Redis版本：{$redis_version}");
    }
}
