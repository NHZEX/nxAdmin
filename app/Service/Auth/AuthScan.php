<?php
declare(strict_types=1);

namespace app\Service\Auth;

use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\Reader;
use think\App;

class AuthScan
{
    use InteractsWithScanAuth;
    use InteractsWithSyncModel;

    const ROOT_NODE = '__ROOT__';

    /**
     * @var App
     */
    protected $app;

    /**
     * @var Reader
     */
    protected $reader;

    /**
     * @var Permission
     */
    protected $permission;

    /**
     * AuthScan constructor.
     * @param App $app
     * @throws AnnotationException
     */
    public function __construct(App $app)
    {
        $this->app = $app;

        $this->reader = new AnnotationReader();

        $this->permission = new Permission();
    }
}
