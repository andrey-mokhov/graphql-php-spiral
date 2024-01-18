<?php

declare(strict_types=1);

namespace Andi\GraphQL\Spiral\Listener;

use Andi\GraphQL\Field\MutationFieldInterface;
use Andi\GraphQL\Spiral\Config\GraphQLConfig;
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
        private readonly GraphQLConfig $config,
    ) {
    }

    public function listen(ReflectionClass $class): void
    {
        if ($class->isAbstract() || $class->isTrait() || $class->isEnum()) {
            return;
        }

        $this->classes[] = $class->getName();
    }

    public function finalize(): void
    {
        $type = $this->config->getMutationType();

        $mutation = null !== $type && $this->typeRegistry->has($type)
            ? $this->typeRegistry->get($type)
            : null;

        if (! $mutation instanceof DynamicObjectTypeInterface) {
            return;
        }

        foreach ($this->classes as $class) {
            $mutation->addAdditionalField($this->container->get($class));
        }
    }
}
