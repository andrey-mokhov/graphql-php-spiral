<?php

declare(strict_types=1);

namespace Andi\GraphQL\Spiral\Listener;

use Andi\GraphQL\Attribute\MutationField;
use Andi\GraphQL\Spiral\Config\GraphQLConfig;
use Andi\GraphQL\Type\DynamicObjectTypeInterface;
use Andi\GraphQL\TypeRegistryInterface;
use Spiral\Attributes\ReaderInterface;
use Spiral\Tokenizer\Attribute\TargetAttribute;

#[TargetAttribute(MutationField::class)]
final class AttributedMutationFieldListener extends AbstractAdditionalFieldListener
{
    protected string $attribute = MutationField::class;

    public function __construct(
        ReaderInterface $reader,
        TypeRegistryInterface $typeRegistry,
        private readonly GraphQLConfig $config,
    ) {
        parent::__construct($reader, $typeRegistry);
    }

    public function finalize(): void
    {
        $type = $this->config->getMutationType();

        $mutation = null !== $type && $this->typeRegistry->has($type)
            ? $this->typeRegistry->get($type)
            : null;

        if ($mutation instanceof DynamicObjectTypeInterface) {
            foreach ($this->methods as $method) {
                $mutation->addAdditionalField($method);
            }
        }
    }
}
