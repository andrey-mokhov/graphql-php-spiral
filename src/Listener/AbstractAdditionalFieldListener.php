<?php

declare(strict_types=1);

namespace Andi\GraphQL\Spiral\Listener;

use Andi\GraphQL\Attribute\AbstractField;
use Andi\GraphQL\Common\ReflectionMethodWithAttribute;
use Andi\GraphQL\TypeRegistryInterface;
use Spiral\Attributes\ReaderInterface;
use Spiral\Tokenizer\TokenizationListenerInterface;

abstract class AbstractAdditionalFieldListener implements TokenizationListenerInterface
{
    /**
     * @var array<ReflectionMethodWithAttribute>
     */
    protected array $methods = [];

    /** @var class-string<AbstractField> */
    protected string $attribute;

    public function __construct(
        protected readonly ReaderInterface $reader,
        protected readonly TypeRegistryInterface $typeRegistry,
    ) {
    }

    public function listen(\ReflectionClass $class): void
    {
        if ($class->isAbstract() || $class->isEnum() || $class->isTrait()) {
            return;
        }

        foreach ($class->getMethods() as $method) {
            if ($method->getDeclaringClass()->getName() !== $class->getName()) {
                continue;
            }

            if ($attribute = $this->reader->firstFunctionMetadata($method, $this->attribute)) {
                $this->methods[] = new ReflectionMethodWithAttribute($method, $attribute);
            }
        }
    }
}
