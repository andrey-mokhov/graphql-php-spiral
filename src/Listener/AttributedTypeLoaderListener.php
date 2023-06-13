<?php

declare(strict_types=1);

namespace Andi\GraphQL\Spiral\Listener;

use Andi\GraphQL\Attribute\InputObjectType;
use Andi\GraphQL\Attribute\ObjectField;
use Andi\GraphQL\Attribute\ObjectType;
use Andi\GraphQL\Exception\CantResolveGraphQLTypeException;
use Andi\GraphQL\Type\DynamicObjectTypeInterface;
use Andi\GraphQL\TypeRegistryInterface;
use Andi\GraphQL\TypeResolver\TypeResolverInterface;
use ReflectionClass;
use Spiral\Attributes\ReaderInterface;
use Spiral\Tokenizer\Attribute\TargetAttribute;
use Spiral\Tokenizer\TokenizationListenerInterface;

#[TargetAttribute(attribute: ObjectType::class)]
#[TargetAttribute(attribute: InputObjectType::class)]
final class AttributedTypeLoaderListener implements TokenizationListenerInterface
{
    public function __construct(
        private readonly ReaderInterface $reader,
        private readonly TypeRegistryInterface $typeRegistry,
        private readonly TypeResolverInterface $typeResolver,
    ) {
    }

    public function listen(ReflectionClass $class): void
    {
        try {
            $type = $this->typeResolver->resolve($class);
        } catch (CantResolveGraphQLTypeException) {
            return;
        }

        $typeName = (string) $type;

        if (! $this->typeRegistry->has($typeName)) {
            $this->typeRegistry->register($type, $class->getName());
        }

        if ($type instanceof DynamicObjectTypeInterface) {
            $this->registerObjectFields($type, $class);
        }
    }

    public function finalize(): void
    {
    }

    private function registerObjectFields(DynamicObjectTypeInterface $type, ReflectionClass $class): void
    {
        foreach ($class->getMethods() as $method) {
            if (null !== $this->reader->firstFunctionMetadata($method, ObjectField::class)) {
                $type->addAdditionalField($method);
            }
        }

        foreach ($class->getProperties() as $property) {
            if (null !== $this->reader->firstPropertyMetadata($property, ObjectField::class)) {
                $type->addAdditionalField($property);
            }
        }
    }
}
