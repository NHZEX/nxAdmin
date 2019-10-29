<?php
declare(strict_types=1);

namespace app\Service\Auth;

use Generator;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use SplFileInfo;
use Symfony\Component\Finder\Finder;
use think\App;
use think\Config;
use function str_replace;
use function strlen;
use function substr;

/**
 * 控制器入口扫描 - 仅支持单应用
 *
 * Class NodesManage
 * @package app\Service\Auth
 */
class ControllerScan
{
    protected $baseDir;

    protected $controllerDirs = [];

    protected $dirsLength = [];

    protected $controllerName;

    protected $controllerNS = 'app\\';

    /**
     * NodesManage constructor.
     * @param App    $app
     * @param Config $config
     */
    public function __construct(App $app, Config $config)
    {
        $this->baseDir        = $app->getBasePath();
        $this->controllerName = $config->get('route.controller_layer');
        $this->controllerNS   .= $this->controllerName . '\\';

        $this->controllerDirs = [
            $this->baseDir . $this->controllerName . DS,
        ];

        foreach ($this->controllerDirs as $dir) {
            $this->dirsLength[$dir] = strlen($dir);
        }
    }

    public function nodeTree()
    {
        $tree = [];
        foreach ($this->scanning() as $controller) {
            $nodes = [];
            $controllerPath = strtolower(str_replace('\\', '.', $controller['baseName']));
            foreach ($controller['action'] as $action) {
                $nodes[] = [
                    'name' => $controllerPath . '/' . strtolower($action['name']),
                    'docs' =>  $action['docs'],
                ];
            }
            $tree[] = [
                'name' => $controllerPath,
                'docs' => $controller['docs'],
                'nodes' => $nodes,
            ];
        }
        return $tree;
    }

    /**
     * @return Generator
     */
    public function scanning(): Generator
    {
        foreach ($this->controllerDirs as $dir) {
            $finder = new Finder();
            $finder->files()->in($dir)->name('*.php');
            if (!$finder->hasResults()) {
                continue;
            }
            foreach ($finder as $file) {
                if (empty($controller = $this->parseController($dir, $file))) {
                    continue;
                }
                yield $controller;
            }
        }
    }

    public function parseController(string $controllerDir, SplFileInfo $file): ?array
    {
        $controllerPath = substr($file->getPath(), $this->dirsLength[$controllerDir]);
        $controllerPath = str_replace('/', '\\', $controllerPath);
        if (!empty($controllerPath)) {
            $controllerPath .= '\\';
        }

        $baseName = $file->getBasename(".{$file->getExtension()}");
        $className = $this->controllerNS . $controllerPath . $baseName;

        try {
            $class = new ReflectionClass($className);
        } catch (ReflectionException $e) {
            return null;
        }

        if (!$class->isInstantiable()) {
            return null;
        }

        $result = [
            'class' => $class->getName(),
            'docs' => self::parseDoc($class->getDocComment() ?: ''),
            'baseName' => $controllerPath . $baseName,
            'action' => [],
        ];

        foreach ($class->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            // 方法名称开头不能存在下划线
            if (0 === strpos($method->getName(), '_')) {
                continue;
            }
            $result['action'][] = [
                'name' => $method->getName(),
                'docs' => self::parseDoc($method->getDocComment() ?: ''),
            ];
        }

        return $result;
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
