<?php

declare(strict_types=1);

namespace Andi\GraphQL\Spiral\Listener;

use Andi\GraphQL\Field\QueryFieldInterface;
use Andi\GraphQL\Type\DynamicObjectTypeInterface;
use Andi\GraphQL\TypeRegistryInterface;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use Spiral\Tokenizer\Attribute\TargetClass;
use Spiral\Tokenizer\TokenizationListenerInterface;

#[TargetClass(class: QueryFieldInterface::class)]
final class QueryFieldListener implements TokenizationListenerInterface
{
    private array $classes = [];

    public function __construct(
        private readonly ContainerInterface $container,
        private readonly TypeRegistryInterface $typeRegistry,
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
        $queryType = $this->typeRegistry->get('Query');
        if (! $queryType instanceof DynamicObjectTypeInterface) {
            return;
        }

        foreach ($this->classes as $class) {
            $queryType->addAdditionalField($this->container->get($class));
        }
    }
}
