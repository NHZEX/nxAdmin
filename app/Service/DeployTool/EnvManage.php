<?php
declare(strict_types=1);

namespace app\Service\DeployTool;

use app\Facade\Redis;
use app\Server\DeployInfo;
use app\Service\DeployTool\Exception\ConfigInvalidException;
use app\Service\DeployTool\Exception\InputException;
use app\Service\DeployTool\Struct\EnvStruct;
use Closure;
use Exception;
use think\console\Input;
use think\console\Output;
use think\db\exception\BindParamException;
use think\db\exception\PDOException;
use think\helper\Str;

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
            $this->configMysql();
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
     * @param string  $message
     * @throws Exception
     */
    protected function checkInput(Closure $closure, string $message)
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
            } catch (InputException|ConfigInvalidException $error) {
                if ((bool) $this->input->getOption('no-interaction')) {
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
     * @throws BindParamException
     * @throws ConfigInvalidException
     * @throws InputException
     * @throws PDOException
     */
    protected function configMysql()
    {
        $this->output->writeln('> 配置数据库');

        $dbPreset = $this->deploy->getConfig('db');
        $dbPresetItem = $dbPreset['item'];

        // 载入数据库配置
        foreach ($this->envExtract($dbPreset['prefix'], 3) as $data) {
            [$group, $name, $value] = $data;
            if (isset($dbPresetItem[$group])) {
                $dbPresetItem[$group]['conf'][$name][0] = $value;
            }
        };

        foreach ($dbPresetItem as $name => $info) {
            $this->output->writeln(" > 配置库: {$info['desc']}({$name})");
            $result = $this->showFormsInput($info['conf']);
            // 测试配置
            $this->testMysql($name, $result);
            // 转换为常量
            $result = $this->toEnvFormat($dbPreset['prefix'], $name, $result);
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

        $redisPreset = $this->deploy->getConfig('redis');

        // 载入Redis配置
        foreach ($this->envExtract('REDIS', 2) as $data) {
            [, $name, $value] = $data;
            $redisPreset[$name][0] = $value;
        };

        $result = $this->showFormsInput($redisPreset);
        // 测试配置
        $this->testRedis($result);
        // 转换为常量
        $result = $this->toEnvFormat('REDIS', null, $result);
        // 写到常量
        foreach ($result as $key => $value) {
            $this->env->$key = $value;
        }
    }

    /**
     * @throws ConfigInvalidException
     * @throws InputException
     */
    protected function configCache()
    {
        $this->output->writeln('> 配置Cache');

        $cachePreset = $this->deploy->getConfig('cache');
        $cachePresetItem = $cachePreset['item'];

        // 载入数据库配置
        foreach ($this->envExtract($cachePreset['prefix'], 3) as $data) {
            [$group, $name, $value] = $data;
            if (isset($cachePresetItem[$group])) {
                $cachePresetItem[$group]['conf'][$name][0] = $value;
            }
        };

        $config = [];
        // TODO 暂时只支持redis驱动
        if ($this->output->confirm($this->input, '是否复用Redis配置', true)) {
            // 载入Redis配置
            foreach ($this->envExtract('REDIS', 2) as $data) {
                [, $name, $value] = $data;
                $config[$name] = $value;
            };
        } else {
            $config = $this->showFormsInput($cachePresetItem['redis']['conf']);
        }
        // 测试配置
        $this->testRedis($config);
        // 写入缓存组件
        $cacheConfig = $this->app->config->get('cache');
        $cacheConfig['stores']['redis'] = array_merge($cacheConfig['stores']['redis'], $config);
        $this->app->cache->config($cacheConfig);
        // 转换为常量
        $config = $this->toEnvFormat($cachePreset['prefix'], 'redis', $config);
        // 写到常量
        foreach ($config as $key => $value) {
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
        };

        $config = [];
        if ($this->output->confirm($this->input, '是否复用Redis配置', true)) {
            // 载入Redis配置
            foreach ($this->envExtract('REDIS', 2) as $data) {
                [, $name, $value] = $data;
                $config["redis_{$name}"] = $value;
            };
            // 移除redis输入;
            $cachePreset = array_filter($cachePreset, function ($value, $key) {
                return !Str::startsWith($key, 'redis_');
            }, ARRAY_FILTER_USE_BOTH);
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

    protected function configLog()
    {
        // TODO 日志配置管理
    }

    /**
     * 测试数据库连接
     * TODO 扩展更多连接类型支持
     * @param string $connections
     * @param array  $testConfig
     * @throws BindParamException
     * @throws ConfigInvalidException
     * @throws PDOException
     */
    protected function testMysql(string $connections, array $testConfig)
    {
        $config = $this->app->config->get('database');
        $config['connections'][$connections] = array_merge($config['connections'][$connections], $testConfig);
        $this->app->db->setConfig($config);

        // 检查mysql版本
        $mysql_ver = query_mysql_version($connections);

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
        Redis::setConfig($config, true);
        if (Redis::instance()->ping() !== '+PONG') {
            throw new ConfigInvalidException('Redis测试失败');
        }

        $redis_version = Redis::instance()->getServerVersion();
        if (version_compare($redis_version, self::REDIS_VER_LIMIT, '<')) {
            $errmsg = "当前连接Redis版本：{$redis_version}，最小限制版本：" . self::REDIS_VER_LIMIT;
            throw new ConfigInvalidException($errmsg);
        }
        $this->output->writeln("当前配置Redis版本：{$redis_version}");
    }
}
