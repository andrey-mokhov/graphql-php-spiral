<?php

declare(strict_types=1);

namespace Andi\Tests\GraphQL\Spiral\dirs\app\src;

use Andi\GraphQL\Attribute\ObjectField;
use Andi\GraphQL\Attribute\ObjectType;
use Andi\GraphQL\Definition\Type\InterfacesAwareInterface;

/**
 * @internal
 * @psalm-internal Andi\Tests\GraphQL\Spiral
 */
#[ObjectType]
final class User implements InterfacesAwareInterface
{
    public function __construct(
        #[ObjectField] public readonly string $lastname,
        #[ObjectField] public readonly string $firstname,
    ) {
    }

    public function getInterfaces(): iterable
    {
        yield UserInterface::class;
    }
}
