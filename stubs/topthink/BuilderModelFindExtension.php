<?php
declare(strict_types=1);

namespace stubs\topthink;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\Type;
use think\Model;
use function var_dump;

class BuilderModelFindExtension implements DynamicMethodReturnTypeExtension
{

    public function getClass(): string
    {
        return Model::class;
    }

    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        var_dump($methodReflection->getDeclaringClass()->getName() . '::' . $methodReflection->getName());
        return false;
    }

    public function getTypeFromMethodCall(
        MethodReflection $methodReflection,
        MethodCall $methodCall,
        Scope $scope
    ): Type {
        // TODO: Implement getTypeFromMethodCall() method.
    }
}
