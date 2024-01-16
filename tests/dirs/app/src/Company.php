<?php

declare(strict_types=1);

namespace Andi\Tests\GraphQL\Spiral\dirs\app\src;

use Andi\GraphQL\Attribute\ObjectField;
use Andi\GraphQL\Attribute\ObjectType;

/**
 * @internal
 * @psalm-internal Andi\Tests\GraphQL\Spiral
 */
#[ObjectType]
final class Company
{
    public function __construct(
        #[ObjectField] public readonly string $name,
    ) {
    }
}
