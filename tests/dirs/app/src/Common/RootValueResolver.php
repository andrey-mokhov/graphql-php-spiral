<?php

declare(strict_types=1);

namespace Andi\Tests\GraphQL\Spiral\dirs\app\src\Common;

/**
 * @internal
 * @psalm-internal Andi\Tests\GraphQL\Spiral
 */
final class RootValueResolver
{
    public function __invoke(): string
    {
        return 'rootValue';
    }
}
