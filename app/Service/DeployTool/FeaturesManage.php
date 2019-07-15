<?php
declare(strict_types=1);

namespace app\Service\DeployTool;

use app\Struct\EnvStruct;
use Closure;
use Exception;
use think\App;
use think\console\Input;
use think\console\Output;
use think\console\output\Ask;
use think\console\output\Question;
use think\helper\Str;

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
     * @return array
     */
    abstract public function getActionList(): array;

    /**
     * @param Input  $input
     * @param Output $output
     * @param        $option
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
        } else {
            $runAction = Str::camel('action_' . $runAction);
            $this->app->invokeFunction(
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
}
