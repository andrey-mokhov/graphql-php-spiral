<?php

declare(strict_types=1);

namespace Andi\GraphQL\Spiral\Listener;

use Andi\GraphQL\Attribute\AdditionalField;
use Andi\GraphQL\Type\DynamicObjectTypeInterface;
use Andi\GraphQL\TypeRegistryInterface;
use GraphQL\Type\Definition as Webonyx;
use ReflectionClass;
use ReflectionMethod;
use Spiral\Attributes\ReaderInterface;
use Spiral\Tokenizer\Attribute\TargetAttribute;
use Spiral\Tokenizer\TokenizationListenerInterface;

#[TargetAttribute(AdditionalField::class)]
final class AdditionalFieldListener implements TokenizationListenerInterface
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
            if (null !== $this->reader->firstFunctionMetadata($method, AdditionalField::class)) {
                $this->methods[] = $method;
            }
        }
    }

    public function finalize(): void
    {
        foreach ($this->methods as $method) {
            if ($attribute = $this->reader->firstFunctionMetadata($method, AdditionalField::class)) {
                $type = $this->typeRegistry->get($attribute->targetType);

                $isExtensibleType = $type instanceof DynamicObjectTypeInterface && (
                    $type instanceof Webonyx\ObjectType
                    || $type instanceof Webonyx\InterfaceType
                    || $type instanceof Webonyx\InputObjectType
                );

                if ($isExtensibleType) {
                    $type->addAdditionalField($method);
                }
            }
        }

        unset($this->methods);
    }
}
