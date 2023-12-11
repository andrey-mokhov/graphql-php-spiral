<?php

declare(strict_types=1);

namespace Andi\GraphQL\Spiral\Listener;

use Andi\GraphQL\Attribute\AbstractType;
use Andi\GraphQL\Attribute\EnumType;
use Andi\GraphQL\Attribute\InputObjectType;
use Andi\GraphQL\Attribute\InterfaceType;
use Andi\GraphQL\Attribute\ObjectType;
use Andi\GraphQL\Exception\CantResolveGraphQLTypeException;
use Andi\GraphQL\TypeRegistryInterface;
use Andi\GraphQL\TypeResolver\TypeResolverInterface;
use GraphQL\Type\Definition\NamedType;
use ReflectionClass;
use Spiral\Attributes\ReaderInterface;
use Spiral\Tokenizer\Attribute\TargetAttribute;
use Spiral\Tokenizer\TokenizationListenerInterface;

#[TargetAttribute(attribute: ObjectType::class)]
#[TargetAttribute(attribute: InputObjectType::class)]
#[TargetAttribute(attribute: EnumType::class)]
#[TargetAttribute(attribute: InterfaceType::class)]
final class AttributedTypeLoaderListener implements TokenizationListenerInterface
{
    public function __construct(
        private readonly TypeRegistryInterface $typeRegistry,
        private readonly TypeResolverInterface $typeResolver,
        private readonly ReaderInterface $reader,
    ) {
    }

    public function listen(ReflectionClass $class): void
    {
        if (! $this->isValid($class)) {
            return;
        }

        try {
            $type = $this->typeResolver->resolve($class);
        } catch (CantResolveGraphQLTypeException) {
            return;
        }

        $typeName = (string) $type;

        if (! $this->typeRegistry->has($typeName)) {
            assert($type instanceof NamedType);
            $this->typeRegistry->register($type, $class->getName());
        }
    }

    public function finalize(): void
    {
    }

    private function isValid(ReflectionClass $class): bool
    {
        $attribute = $this->reader->firstClassMetadata($class, AbstractType::class);

        if ($attribute instanceof ObjectType || $attribute instanceof InputObjectType) {
            return ! $class->isAbstract() && ! $class->isTrait() && ! $class->isEnum();
        }

        if ($attribute instanceof EnumType) {
            return $class->isEnum();
        }

        if ($attribute instanceof InterfaceType) {
            return $class->isInterface();
        }

        return false;
    }
}
