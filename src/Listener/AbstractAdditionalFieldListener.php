<?php

declare(strict_types=1);

namespace Andi\GraphQL\Spiral\Listener;

use Andi\GraphQL\Attribute\AbstractField;
use Andi\GraphQL\Type\DynamicObjectTypeInterface;
use Andi\GraphQL\TypeRegistryInterface;
use ReflectionClass;
use ReflectionMethod;
use Spiral\Attributes\ReaderInterface;
use Spiral\Tokenizer\TokenizationListenerInterface;

abstract class AbstractAdditionalFieldListener implements TokenizationListenerInterface
{
    /**
     * @var array<ReflectionMethod>
     */
    private array $methods = [];

    /** @var class-string<AbstractField> */
    protected string $attribute;

    protected string $targetType;

    public function __construct(
        protected readonly ReaderInterface $reader,
        protected readonly TypeRegistryInterface $typeRegistry,
    ) {
    }

    public function listen(ReflectionClass $class): void
    {
        if ($class->isAbstract() || $class->isEnum() || $class->isTrait()) {
            return;
        }

        foreach ($class->getMethods() as $method) {
            if ($method->getDeclaringClass()->getName() !== $class->getName()) {
                continue;
            }

            if (null !== $this->reader->firstFunctionMetadata($method, $this->attribute)) {
                $this->methods[] = $method;
            }
        }
    }

    public function finalize(): void
    {
        $query = $this->typeRegistry->get($this->targetType);
        if ($query instanceof DynamicObjectTypeInterface) {
            foreach ($this->methods as $method) {
                $query->addAdditionalField($method);
            }
        }
    }
}
