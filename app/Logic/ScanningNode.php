<?php
/**
 * Created by PhpStorm.
 * User: NHZEXG
 * Date: 2019/2/23
 * Time: 17:41
 */

namespace app\Logic;

use Generator;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use think\facade\App;
use think\facade\Config;

class ScanningNode
{
    protected $controller = null;
    protected $deny_controller_list = [
        'Base',
    ];
    protected $file_ext = 'php';

    public function __construct()
    {
        $this->controller = DS . Config::get('route.controller_layer') . DS;
        $this->deny_controller_list = array_flip($this->deny_controller_list);
    }

    /**
     * @param string $father_controller_dir   // 待搜索的控制器目录
     * @param array  $father_controller_names // 以得知的父级控制器名称组
     * @return Generator
     */
    protected function nextModuleDepth(
        string $father_controller_dir,
        array $father_controller_names
    ) {
        $dir = opendir($father_controller_dir);
        while (false !== ($controller_name = readdir($dir))) {
            // 忽略无效文件
            if ('.' === $controller_name || '..' === $controller_name) {
                continue;
            }
            // 组装路径
            $controller_new = $father_controller_dir . DS . $controller_name;
            // 通过递归处理无限极控制器目录
            if (is_dir($controller_new)) {
                // 处理多级控制器
                $up_controller = $father_controller_names;
                $up_controller[] = $controller_name;
                foreach ($this->nextModuleDepth($controller_new, $up_controller) as $value) {
                    yield $value;
                }
                continue;
            }
            // 处理当前目录下的控制器
            if (is_file($controller_new)) {
                $path_info = pathinfo($controller_new);
                if ($path_info
                    && $this->file_ext === $path_info['extension']
                    && !isset($this->deny_controller_list[$path_info['filename']])
                ) {
                    // 生成有效控制器类的信息
                    $up_controller = $father_controller_names;
                    $up_controller[] = $path_info['filename'];
                    $result = [
                        'class' => "app\\controller\\" . join('\\', $up_controller),
                        'controller' => $up_controller,
                        'actions' => [],
                        'actions_info' => [],
                        'controller_info' => null,
                    ];
                    yield $result;
                }
            }
        }
        closedir($dir);
    }

    /**
     * 生成器 控制器名称
     * @param $app_path
     * @return Generator
     */
    protected function obtainController(string $app_path)
    {
        foreach ($this->nextModuleDepth($app_path . $this->controller, []) as $result) {
            yield $result;
        }
    }

    /**
     * 解析 模型 - 控制器 - 方法
     * @return Generator
     * @throws ReflectionException
     */
    public static function parseMca()
    {
        $that = new self();
        $app_path = realpath(App::getAppPath()) . DS;
        foreach ($that->obtainController($app_path) as $mca_value) {
            try {
                $reflection = new ReflectionClass($mca_value['class']);

                // 检查类=是否可实例化
                if (!$reflection->isInstantiable()) {
                    continue;
                }

                $mca_value['controller_info'] = self::parseDoc($reflection->getDocComment());
                $action_arr = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);

                foreach ($action_arr as $action) {
                    // 必须是同一个类
                    if ($action->class !== $mca_value['class']) {
                        continue;
                    }
                    // 方法名称开头不能存在下划线
                    if (0 === strpos($action->name, '_')) {
                        continue;
                    }
                    // 添加到方法集合
                    $mca_value['actions'][] = $action->name;
                    // 尝试获取描述信息
                    $action_info = self::parseDoc($action->getDocComment());
                    // 添加描述信息
                    $action_info && $mca_value['actions_info'][$action->name] = $action_info;
                }
                yield $mca_value;
            } catch (ReflectionException $reflectionException) {
                throw $reflectionException;
            }
        }
    }

    /**
     * 解析doc
     * @param $doc
     * @return array|null 信息数组 {'name'=>'名称', 'desc'=>'说明'}
     */
    public static function parseDoc(string $doc): ?array
    {
        static $doc_regular = '/\*\s\$(name|desc)([\S\s]+?)$/m';

        if (preg_match_all($doc_regular, $doc, $match_doc, PREG_SET_ORDER)) {
            $result = [];
            foreach ($match_doc as $value) {
                $result[trim($value[1])] = trim($value[2]);
            }
            return $result;
        }
        return null;
    }
}
