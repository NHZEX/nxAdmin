<?php
declare(strict_types=1);

namespace app\Service\Auth;

use app\Service\Auth\Annotation\Auth;
use Doctrine\Common\Annotations\Reader;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use SplFileInfo;
use Symfony\Component\Finder\Finder;
use think\App;
use function array_map;
use function str_replace;
use function strlen;
use function substr;

/**
 * Trait InteractsWithAuth
 * @package app\Service\Auth
 * @property App    $app
 * @property Reader $reader
 */
trait InteractsWithAuth
{
    protected $baseDir;
    protected $controllerLayer;
    protected $apps = [];

    protected $namespaces = 'app\\';

    protected $nodes = [];

    /**
     * @throws ReflectionException
     */
    public function scanAuthAnnotation()
    {
        $this->baseDir         = $this->app->getBasePath();
        $this->controllerLayer = $this->app->config->get('route.controller_layer');
        $this->apps = ['test'];

        $dirs = array_map(function ($app) {
            return $this->baseDir . $app . DIRECTORY_SEPARATOR . $this->controllerLayer;
        }, $this->apps);
        $dirs[] = $this->baseDir . $this->controllerLayer . DS;

        return $this->sancAuthAnnotation($dirs);
    }

    /**
     * @param $dirs
     * @return array
     * @throws ReflectionException
     */
    protected function sancAuthAnnotation($dirs)
    {
        $permissions = [];
        $this->nodes = [];

        foreach ($this->scanning($dirs) as $file) {
            /** @var SplFileInfo $file */
            $class = $this->parseClassName($file);
            $refClass = new ReflectionClass($class);
            if ($refClass->isAbstract() || $refClass->isTrait()) {
                continue;
            }
            // 是否多应用
            $isApp = (0 !== strpos($class, $this->namespaces . $this->controllerLayer));

            if ($isApp) {
                $controllerUrl = substr($class, strlen($this->namespaces));
                $appPos = strpos($controllerUrl, '\\');
                $appName = substr($controllerUrl, 0, $appPos);
                $controllerUrl = substr($controllerUrl, $appPos + strlen($this->controllerLayer . '\\') + 1);
                $controllerUrl = $appName. '/' . strtolower(str_replace('\\', '.', $controllerUrl));
            } else {
                $controllerUrl = substr($class, strlen($this->namespaces . $this->controllerLayer . '\\'));
                $controllerUrl = strtolower(str_replace('\\', '.', $controllerUrl));
            }


            foreach ($refClass->getMethods(ReflectionMethod::IS_PUBLIC) as $refMethod) {
                if ($refMethod->isStatic()) {
                    continue;
                }
                if (0 === strpos($refMethod->getName(), '_')) {
                    continue;
                }

                $nodeUrl = $controllerUrl . '/' . strtolower($refMethod->getName());
                $methodPath = $class . '::' . $refMethod->getName();
                // $nodeDesc = $refMethod->getDocComment();

                /** @var Auth $auth */
                if ($auth = $this->reader->getMethodAnnotation($refMethod, Auth::class)) {
                    if (empty($auth->value)) {
                        $permissions['login'][$methodPath] = $class;
                    } else {
                        $authStr = $this->parseAuth($auth->value, $controllerUrl, $refMethod->getName());
                        $permissions[$authStr][$methodPath] = $nodeUrl;
                    }
                }

                $this->nodes[$class][$refMethod->getName()] = $nodeUrl;
            }
        }

        return $permissions;
    }

    protected function parseAuth($auth, $controllerUrl, $methodName)
    {
        if ('self' === $auth) {
            return str_replace('/', '.', $controllerUrl) . '.' . strtolower($methodName);
        }
        return $auth;
    }

    /**
     * @return array
     */
    public function getNodes(): array
    {
        return $this->nodes;
    }

    protected function scanning($dirs)
    {
        $finder = new Finder();
        $finder->files()->in($dirs)->name('*.php');
        if (!$finder->hasResults()) {
            return;
        }
        foreach ($finder as $file) {
            yield $file;
        }
    }

    /**
     * 解析类命名（仅支持Psr4）
     * @param SplFileInfo $file
     * @return string
     */
    protected function parseClassName(SplFileInfo $file): string
    {
        $controllerPath = substr($file->getPath(), strlen($this->baseDir));

        $controllerPath = str_replace('/', '\\', $controllerPath);
        if (!empty($controllerPath)) {
            $controllerPath .= '\\';
        }

        $baseName = $file->getBasename(".{$file->getExtension()}");
        $className = $this->namespaces . $controllerPath . $baseName;
        return $className;
    }
}
