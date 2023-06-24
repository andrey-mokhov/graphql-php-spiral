<?php

declare(strict_types=1);

namespace Andi\GraphQL\Spiral\Listener;

use Andi\GraphQL\Attribute\QueryField;
use Spiral\Tokenizer\Attribute\TargetAttribute;

#[TargetAttribute(QueryField::class)]
final class AttributedQueryFieldListener extends AbstractAdditionalFieldListener
{
    protected string $attribute = QueryField::class;

    protected string $targetType = 'Query';
}
