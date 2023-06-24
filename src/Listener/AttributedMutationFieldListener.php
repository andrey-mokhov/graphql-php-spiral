<?php

declare(strict_types=1);

namespace Andi\GraphQL\Spiral\Listener;

use Andi\GraphQL\Attribute\MutationField;
use Andi\GraphQL\Type\DynamicObjectTypeInterface;
use ReflectionMethod;
use Spiral\Tokenizer\Attribute\TargetAttribute;

#[TargetAttribute(MutationField::class)]
final class AttributedMutationFieldListener extends AbstractAdditionalFieldListener
{
    /**
     * @var array<int,ReflectionMethod>
     */
    private array $methods = [];

    protected string $attribute = MutationField::class;

    public function finalize(): void
    {
        $query = $this->typeRegistry->has('Mutation')
            ? $this->typeRegistry->get('Mutation')
            : null;

        if ($query instanceof DynamicObjectTypeInterface) {
            foreach ($this->methods as $method) {
                $query->addAdditionalField($method);
            }
        }
    }
}
