<?php

declare(strict_types=1);

namespace Andi\Tests\GraphQL\Spiral\Fixture;

use Andi\GraphQL\Attribute\AdditionalField;

/**
 * @internal
 * @psalm-internal Andi\Tests\GraphQL\Spiral
 */
trait TraitForListeners
{
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
}
