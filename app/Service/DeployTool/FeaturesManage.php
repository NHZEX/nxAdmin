<?php
declare(strict_types=1);

namespace app\Service\DeployTool;

use app\Service\DeployTool\Exception\InputException;
use app\Service\DeployTool\Struct\EnvStruct;
use Closure;
use Exception;
use Generator;
use think\App;
use think\console\Input;
use think\console\Output;
use think\console\output\Ask;
use think\console\output\Question;
use think\helper\Str;
use function array_pad;

abstract class FeaturesManage
{
    /**
     * @var App
     */
    protected $app;
    /**
     * @var Deploy
     */
    protected $deploy;
    /**
     * @var EnvStruct
     */
    protected $env;

    /**
     * @var Input
     */
    protected $input;
    /**
     * @var Output
     */
    protected $output;
    /**
     * @var array
     */
    protected $option;

    /**
     * FeaturesManage constructor.
     * @param Deploy    $deploy
     * @param EnvStruct $envStruct
     */
    public function __construct(Deploy $deploy, EnvStruct $envStruct)
    {
        $this->deploy = $deploy;
        $this->env = $envStruct;
        $this->app = $deploy->getApp();
    }

    /**
     * 指令列表
     * @return array
     */
    abstract public function getActionList(): array;

    /**
     * 默认指令
     * @return string
     */
    abstract public function getDefaultAction(): string;

    /**
     * @param Input  $input
     * @param Output $output
     * @param        $option
     * @return bool|int|null
     * @throws Exception
     */
    public function __invoke(Input $input, Output $output, $option)
    {
        $action = array_shift($option);

        $this->input = $input;
        $this->output = $output;
        $this->option = $option;

        $runAction = $this->deploy->autoAction($action, $this->getActionList());

        if (null === $runAction) {
            $this->deploy->showActionList($this->getActionList());
            return true;
        } else {
            $runAction = Str::camel('action_' . $runAction);
            return $this->app->invokeFunction(
                Closure::fromCallable([$this, $runAction]),
                ['input' => $input, 'output' =>  $output, 'option' =>  $option]
            );
        }
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

    /**
     * @param array $info
     * @return array
     * @throws InputException
     */
    protected function showFormsInput(array $info)
    {
        $data = [];
        // 处理输入错误
        foreach ($info as $key => $input) {
            [$value, $desc, $type, $verify] = array_pad($input, 4, null);
            if (empty($type)) {
                $data[$key] = $value;
                continue;
            }

            switch ($type) {
                case 'text':
                    $result = $this->inputText((string) $value, $desc, $verify);
                    break;
                case 'int':
                    $result = (int) $this->inputText($value, $desc, $verify);
                    break;
                case 'password':
                    // TODO password
                    $result = '';
                    break;
                default:
                    throw new InputException("无法处理的输入类型: {$key} => {$type}");
            }

            $data[$key] = $result;
        }

        return $data;
    }

    /**
     * @param string $envPrefix
     * @param int    $segments
     * @return Generator
     */
    protected function envExtract(string $envPrefix, int $segments): Generator
    {
        if ($segments <= 1) {
            return null;
        }
        $envPrefix = strtoupper($envPrefix);
        foreach ($this->env as $key => $value) {
            // 解析三段式常量
            $ekey = explode('_', $key);
            if (count($ekey) < $segments) {
                continue;
            }
            if ($segments === 2) {
                $prefix = array_shift($ekey);
                $name = join('_', $ekey);
                $group = null;
            } else {
                $prefix = array_shift($ekey);
                $group = array_shift($ekey);
                $name = join('_', $ekey);
                $group = strtolower($group);
            }
            // 筛选有效数据库段
            if ($envPrefix === $prefix) {
                $name = strtolower($name);
                yield [$group, $name, $value, $key];
            }
        }
    }

    /**
     * @param string $prefix
     * @param string $name
     * @param array  $data
     * @return array
     */
    protected function toEnvFormat(string $prefix, ?string $name, array $data)
    {
        $prefix = strtoupper($prefix);
        if (null !== $name) {
            $name = strtoupper($name);
        }
        $result = [];
        foreach ($data as $key => $value) {
            $key = strtoupper($key);
            if (null !== $name) {
                $result["{$prefix}_{$name}_{$key}"] = $value;
            } else {
                $result["{$prefix}_{$key}"] = $value;
            }
        }
        return $result;
    }

    /**
     * @param string|int $value
     * @param string $desc
     * @param string $verify
     * @return string|int
     */
    protected function inputText($value, string $desc, ?string $verify)
    {
        $question = new Question("{$desc}\t", $value);
        $question->setValidator(function ($value) use ($verify) {
            if (false === empty($verify)) {
                $this->checkValue($value, $verify);
            }
            return $value;
        });
        $question->setMaxAttempts(3);
        return $this->askQuestion($this->input, $this->output, $question);
    }

    protected function inputPassword($value, $desc, $verify): string
    {
        $question = new Question("{$desc}\t", $value);
        $question->setValidator(function ($value) use ($verify) {
            if (false === empty($verify)) {
                $this->checkValue($value, $verify);
            }
            return $value;
        });
        $question->setMaxAttempts(3);
        return $this->askQuestion($this->input, $this->output, $question);
    }

    /**
     * @param $value
     * @param $verify
     * @throws InputException
     */
    public function checkValue($value, $verify): void
    {
        $validate = $this->app->validate;

        if (is_string($verify)) {
            $verifyNew = [];
            foreach (explode('|', $verify) as $rule) {
                $rule = explode(':', $rule);
                if (count($rule) === 1) {
                    $verifyNew[] = $rule[0];
                } else {
                    $verifyNew[$rule[0]] = $rule[1];
                }
            }
            $verify = $verifyNew;
        }
        foreach ($verify as $rule => $param) {
            if (is_numeric($rule)) {
                $rule = $param;
                $param = '';
            }
            if (!method_exists($validate, $rule)) {
                if (empty($param)) {
                    $param = [];
                } elseif (!is_array($param)) {
                    $param =  explode(',', $param);
                }
                $param = [$value, $rule, $param];
                $rule = 'is';
            } else {
                $param = [$value, $param];
            }
            if (false == $validate->$rule(...$param)) {
                throw new InputException('输入值不合符规范');
            }
        }
    }
}
