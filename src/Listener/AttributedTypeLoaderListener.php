<?php

declare(strict_types=1);

namespace Andi\GraphQL\Spiral\Listener;

use Andi\GraphQL\Attribute\InputObjectType;
use Andi\GraphQL\Attribute\ObjectType;
use Andi\GraphQL\Exception\CantResolveGraphQLTypeException;
use Andi\GraphQL\TypeRegistryInterface;
use Andi\GraphQL\TypeResolver\TypeResolverInterface;
use ReflectionClass;
use Spiral\Tokenizer\Attribute\TargetAttribute;
use Spiral\Tokenizer\TokenizationListenerInterface;

#[TargetAttribute(attribute: ObjectType::class)]
#[TargetAttribute(attribute: InputObjectType::class)]
final class AttributedTypeLoaderListener implements TokenizationListenerInterface
{
    public function __construct(
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
    }

    public function finalize(): void
    {
    }
}
