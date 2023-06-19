<?php

declare(strict_types=1);

namespace Andi\GraphQL\Spiral\Listener;

use Andi\GraphQL\Attribute\QueryField;
use Andi\GraphQL\Type\DynamicObjectTypeInterface;
use Andi\GraphQL\TypeRegistryInterface;
use ReflectionClass;
use ReflectionMethod;
use Spiral\Attributes\ReaderInterface;
use Spiral\Tokenizer\Attribute\TargetAttribute;
use Spiral\Tokenizer\TokenizationListenerInterface;

#[TargetAttribute(QueryField::class)]
final class AttributedQueryFieldListener implements TokenizationListenerInterface
{
    /**
     * @var array<int,ReflectionMethod>
     */
    private array $methods = [];

    public function __construct(
        private readonly ReaderInterface $reader,
        private readonly TypeRegistryInterface $typeRegistry,
    ) {
    }

    public function listen(ReflectionClass $class): void
    {
        foreach ($class->getMethods() as $method) {
            if (null !== $this->reader->firstFunctionMetadata($method, QueryField::class)) {
                $this->methods[] = $method;
            }
        }
    }

    public function finalize(): void
    {
        $query = $this->typeRegistry->get('Query');
        if ($query instanceof DynamicObjectTypeInterface) {
            foreach ($this->methods as $method) {
                $query->addAdditionalField($method);
            }
        }

        unset($this->methods);
    }
}
