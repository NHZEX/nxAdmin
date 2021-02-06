<?php
declare(strict_types=1);

namespace stubs\topthink;

use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\MethodsClassReflectionExtension;
use PHPStan\Reflection\ReflectionProvider;
use think\db\BaseQuery;
use think\Model;
use function var_dump;

class ModelForwardsCallsExtension implements MethodsClassReflectionExtension
{
    /** @var ReflectionProvider */
    private $reflectionProvider;

    public function __construct(ReflectionProvider $provider)
    {
        $this->reflectionProvider = $provider;
    }

    public function hasMethod(ClassReflection $classReflection, string $methodName): bool
    {
        if ($classReflection->getName() !== Model::class && ! $classReflection->isSubclassOf(Model::class)) {
            return false;
        }
        var_dump($classReflection->getName() . '::' . $methodName);
        var_dump($this->reflectionProvider
            ->getClass(BaseQuery::class)
            ->hasNativeMethod($methodName));
        return false;
    }

    public function getMethod(ClassReflection $classReflection, string $methodName): MethodReflection
    {
        // TODO: Implement getMethod() method.
    }
}
