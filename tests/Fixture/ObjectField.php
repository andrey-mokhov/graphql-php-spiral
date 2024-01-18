<?php

declare(strict_types=1);

namespace Andi\Tests\GraphQL\Spiral\Fixture;

use GraphQL\Type\Definition as Webonyx;

/**
 * @internal
 * @psalm-internal Andi\Tests\GraphQL\Spiral
 */
final class ObjectField extends Webonyx\FieldDefinition
{
    public function __construct()
    {
        parent::__construct(['name' => 'foo']);
    }
}
