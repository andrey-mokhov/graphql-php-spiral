<?php

declare(strict_types=1);

namespace Andi\GraphQL\Spiral\Listener;

use Andi\GraphQL\Attribute\QueryField;
use Andi\GraphQL\Spiral\Config\GraphQLConfig;
use Andi\GraphQL\Type\DynamicObjectTypeInterface;
use Andi\GraphQL\TypeRegistryInterface;
use Spiral\Attributes\ReaderInterface;
use Spiral\Tokenizer\Attribute\TargetAttribute;

#[TargetAttribute(QueryField::class)]
final class AttributedQueryFieldListener extends AbstractAdditionalFieldListener
{
    protected string $attribute = QueryField::class;

    public function __construct(
        ReaderInterface $reader,
        TypeRegistryInterface $typeRegistry,
        private readonly GraphQLConfig $config,
    ) {
        parent::__construct($reader, $typeRegistry);
    }

    public function finalize(): void
    {
        $query = $this->typeRegistry->get($this->config->getQueryType());
        if ($query instanceof DynamicObjectTypeInterface) {
            foreach ($this->methods as $method) {
                $query->addAdditionalField($method);
            }
        }
    }
}
