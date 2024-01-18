<?php

declare(strict_types=1);

namespace Andi\Tests\GraphQL\Spiral\dirs\app\src;

use Andi\GraphQL\Attribute\InputObjectField;
use Andi\GraphQL\Attribute\InputObjectType;
use Andi\GraphQL\Definition\Type\ParseValueAwareInterface;

/**
 * @internal
 * @psalm-internal Andi\Tests\GraphQL\Spiral
 */
#[InputObjectType]
final class CreateUser implements ParseValueAwareInterface
{
    public function __construct(
        #[InputObjectField] public readonly string $lastname,
        #[InputObjectField] public readonly string $firstname,
    ) {
    }

    public static function parseValue(array $values): self
    {
        return new self($values['lastname'], $values['firstname']);
    }
}
