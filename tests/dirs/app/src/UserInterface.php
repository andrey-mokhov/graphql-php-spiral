<?php

declare(strict_types=1);

namespace Andi\Tests\GraphQL\Spiral\dirs\app\src;
use Andi\GraphQL\Attribute\InterfaceType;
use Andi\GraphQL\Attribute\ObjectField;

/**
 * @internal
 * @psalm-internal Andi\Tests\GraphQL\Spiral
 */
#[InterfaceType]
interface UserInterface
{
    #[ObjectField]
    public function getLastname(): string;

    #[ObjectField]
    public function getFirstname(): string;
}
