<?php
declare(strict_types=1);

namespace app\Service\Auth;

use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\Reader;
use think\Service;

class AuthService extends Service
{
    /**
     * @var Reader
     */
    protected $reader;

    /**
     */
    public function register()
    {
        // 注册到容器
        // 注册鉴权中间件

        // TODO: this method is deprecated and will be removed in doctrine/annotations 2.0
        AnnotationRegistry::registerLoader('class_exists');
    }

    public function boot()
    {
    }
}
