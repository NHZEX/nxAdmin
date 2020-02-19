<?php
declare(strict_types=1);

namespace app\Service\Auth\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * 权限注解
 * @package app\Service\Auth\Annotation
 * @Annotation
 * @Annotation\Target({"CLASS", "METHOD"})
 */
final class Auth extends Annotation
{
    /**
     * 定义权限分配
     *
     * @var string
     */
    public $value = 'login';

    /**
     * 定义策略
     *
     * @var string
     */
    public $policy = '';

    /**
     * 功能注解
     *
     * @var string
     */
    public $desc = '';
}
