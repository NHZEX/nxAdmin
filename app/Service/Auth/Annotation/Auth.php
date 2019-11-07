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
}
