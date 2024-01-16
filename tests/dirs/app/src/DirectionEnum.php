<?php

declare(strict_types=1);

namespace Andi\Tests\GraphQL\Spiral\dirs\app\src;

use Andi\GraphQL\Attribute\EnumType;

/**
 * @internal
 * @psalm-internal Andi\Tests\GraphQL\Spiral
 */
#[EnumType]
enum DirectionEnum: string
{
    case asc = 'asc';

    case desc = 'desc';
}
