<?php

declare(strict_types=1);

namespace Andi\GraphQL\Spiral\Listener;

use Andi\GraphQL\Definition\Type;
use Andi\GraphQL\Exception\CantResolveGraphQLTypeException;
use Andi\GraphQL\TypeRegistryInterface;
use Andi\GraphQL\TypeResolver\TypeResolverInterface;
use GraphQL\Type\Definition as Webonyx;
use ReflectionClass;
use Spiral\Tokenizer\Attribute\TargetClass;
use Spiral\Tokenizer\TokenizationListenerInterface;

#[TargetClass(class: Type\EnumTypeInterface::class)]
#[TargetClass(class: Type\InputObjectTypeInterface::class)]
#[TargetClass(class: Type\InterfaceTypeInterface::class)]
#[TargetClass(class: Type\ObjectTypeInterface::class)]
#[TargetClass(class: Type\ScalarTypeInterface::class)]
#[TargetClass(class: Type\UnionTypeInterface::class)]
#[TargetClass(class: Webonyx\Type::class)]
final class TypeLoaderListener implements TokenizationListenerInterface
{
    public function __construct(
        private readonly TypeRegistryInterface $typeRegistry,
        private readonly TypeResolverInterface $typeResolver,
    ) {
    }

    public function listen(ReflectionClass $class): void
    {
        $className = $class->getName();

        if ($this->typeRegistry->has($className)) {
            return;
        }

        try {
            $type = $this->typeResolver->resolve($className);
            $this->typeRegistry->register($type, $className);
        } catch (CantResolveGraphQLTypeException) {
        }
    }

    public function finalize(): void
    {
    }
}
