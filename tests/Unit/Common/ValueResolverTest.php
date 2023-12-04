<?php

declare(strict_types=1);

namespace Andi\Tests\GraphQL\Spiral\Unit\Common;

use Andi\GraphQL\Spiral\Common\ValueResolver;
use GraphQL\Language\AST\DocumentNode;
use GraphQL\Server\OperationParams;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Spiral\Core\Container;
use Spiral\Core\InvokerInterface;
use Spiral\Core\ScopeInterface;

#[CoversClass(ValueResolver::class)]
final class ValueResolverTest extends TestCase
{
    public function testInvokable(): void
    {
        $instance = new ValueResolver(
            \Mockery::mock(ScopeInterface::class),
            \Mockery::mock(InvokerInterface::class),
            fn () => new \stdClass(),
        );

        self::assertIsCallable($instance);
    }

    #[DataProvider('getData')]
    public function testInvoke(mixed $expected, callable $factory): void
    {
        $container = new Container();
        $instance = new ValueResolver($container, $container, $factory);
        $result = $instance(new OperationParams(), new DocumentNode([]), 'query');
        self::assertSame($expected, $result);
    }

    public static function getData(): iterable
    {
        $obj = new \stdClass();

        yield 'factory as Closure' => [
            'expected' => $obj,
            'factory' => fn() => $obj,
        ];

        yield 'factory as invokable object' => [
            'expected' => $obj,
            'factory' => new class ($obj) {
                public function __construct(private readonly object $object)
                {
                }

                public function __invoke()
                {
                    return $this->object;
                }
            },
        ];

        yield 'factory as invokable object with parameters' => [
            'expected' => $obj,
            'factory' => new class ($obj) {
                public function __construct(private readonly object $object)
                {
                }

                public function __invoke(
                    string $operationType,
                    OperationParams $params,
                    DocumentNode $node,
                    ContainerInterface $container,
                ) {
                    return $this->object;
                }
            },
        ];
    }
}
