<?php

declare(strict_types=1);

namespace Andi\Tests\GraphQL\Spiral\Fixture;

use GraphQL\Type\Definition as Webonyx;

final class ObjectType extends Webonyx\ObjectType
{
    public function __construct()
    {
        parent::__construct(['name' => 'foo']);
    }
}
