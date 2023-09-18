<?php

declare(strict_types=1);

namespace Andi\GraphQL\Spiral\Listener;

use Andi\GraphQL\Attribute\AdditionalField;
use Andi\GraphQL\Type\DynamicObjectTypeInterface;
use ReflectionMethod;
use Spiral\Tokenizer\Attribute\TargetAttribute;

#[TargetAttribute(AdditionalField::class)]
final class AdditionalFieldListener extends AbstractAdditionalFieldListener
{
    protected string $attribute = AdditionalField::class;

    public function finalize(): void
    {
        foreach ($this->methods as $method) {
            if ($attribute = $this->reader->firstFunctionMetadata($method, $this->attribute)) {
                $type = $this->typeRegistry->get($attribute->targetType);

                if ($type instanceof DynamicObjectTypeInterface) {
                    $type->addAdditionalField($method);
                }
            }
        }
    }
}
