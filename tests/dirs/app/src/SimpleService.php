<?php

declare(strict_types=1);

namespace Andi\Tests\GraphQL\Spiral\dirs\app\src;

use Andi\GraphQL\Attribute\Argument;
use Andi\GraphQL\Attribute\MutationField;
use Andi\GraphQL\Attribute\QueryField;

/**
 * @internal
 * @psalm-internal Andi\Tests\GraphQL\Spiral
 */
final class SimpleService
{
    #[QueryField(name: 'echo')]
    #[MutationField(name: 'echo')]
    public function echoMessage(#[Argument] string $message): string
    {
        return 'echo: ' . $message;
    }

    #[QueryField(type: 'UserInterface!')]
    public function getUser(): User
    {
        return new User('Gagarin', 'Yuri');
    }

    #[QueryField]
    public function inverseDirection(#[Argument] DirectionEnum $direction): DirectionEnum
    {
        return $direction === DirectionEnum::asc
            ? DirectionEnum::desc
            : DirectionEnum::asc;
    }

    #[MutationField]
    public function registration(#[Argument(name: 'input')] CreateUser $user): User
    {
        return new User($user->lastname, $user->firstname);
    }

    #[QueryField]
    public function everyone(): Company|User
    {
        return new Company('Apple');
    }
}
