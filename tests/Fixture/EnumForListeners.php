<?php

declare(strict_types=1);

namespace Andi\Tests\GraphQL\Spiral\Fixture;

use Andi\GraphQL\Attribute\AdditionalField;
use Andi\GraphQL\Attribute\EnumType;
use Andi\GraphQL\Definition\Field\TypeAwareInterface;
use Andi\GraphQL\Field\MutationFieldInterface;
use Andi\GraphQL\Field\QueryFieldInterface;

/**
 * @internal
 * @psalm-internal Andi\Tests\GraphQL\Spiral
 */
#[EnumType]
enum EnumForListeners: string implements QueryFieldInterface, MutationFieldInterface
{
    case foo = 'foo';
    case bar = 'bar';

    /**
     * Method ignored by AdditionalFieldListener.
     *
     * @return string
     */
    #[AdditionalField(targetType: 'Query')]
    public function getFoo(): string
    {
        return 'foo';
    }

    public function getName(): string
    {
        return 'foo';
    }

    public function getDescription(): ?string
    {
        return null;
    }

    public function getDeprecationReason(): ?string
    {
        return null;
    }

    public function getType(): string
    {
        return 'String';
    }

    public function getMode(): int
    {
        return TypeAwareInterface::IS_REQUIRED;
    }
}
