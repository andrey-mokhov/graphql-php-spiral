<?php

declare(strict_types=1);

namespace Andi\Tests\GraphQL\Spiral\Fixture;

use Andi\GraphQL\Attribute\InterfaceType;
use Andi\GraphQL\Field\MutationFieldInterface;
use Andi\GraphQL\Field\QueryFieldInterface;

/**
 * @internal
 * @psalm-internal Andi\Tests\GraphQL\Spiral
 */
#[InterfaceType]
interface InterfaceForListeners extends QueryFieldInterface, MutationFieldInterface
{
}
