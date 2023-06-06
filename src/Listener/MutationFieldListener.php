<?php

declare(strict_types=1);

namespace Andi\GraphQL\Spiral\Listener;

use Andi\GraphQL\Field\MutationFieldInterface;
use Andi\GraphQL\Type\DynamicObjectTypeInterface;
use Andi\GraphQL\TypeRegistryInterface;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use Spiral\Tokenizer\Attribute\TargetClass;
use Spiral\Tokenizer\TokenizationListenerInterface;

#[TargetClass(class: MutationFieldInterface::class)]
final class MutationFieldListener implements TokenizationListenerInterface
{
    private array $classes = [];

    public function __construct(
        private readonly ContainerInterface $container,
        private readonly TypeRegistryInterface $typeRegistry,
    ) {
    }

    public function listen(ReflectionClass $class): void
    {
        $this->classes[] = $class->getName();
    }

    public function finalize(): void
    {
        if (! $this->typeRegistry->has('Mutation')) {
            return;
        }

        $mutationType = $this->typeRegistry->get('Mutation');
        if (! $mutationType instanceof DynamicObjectTypeInterface) {
            return;
        }

        foreach ($this->classes as $class) {
            $mutationType->addAdditionalField($this->container->get($class));
        }
    }
}
