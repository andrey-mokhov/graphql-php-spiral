<?php

declare(strict_types=1);

namespace Andi\Tests\GraphQL\Spiral\Unit\Listener;

use Andi\GraphQL\Spiral\Listener\TypeLoaderListener;
use Andi\GraphQL\Type\QueryType;
use Andi\GraphQL\TypeRegistry;
use Andi\GraphQL\TypeRegistryInterface;
use Andi\GraphQL\TypeResolver\Middleware\WebonyxGraphQLTypeMiddleware;
use Andi\GraphQL\TypeResolver\TypeResolver;
use Andi\GraphQL\TypeResolver\TypeResolverInterface;
use Andi\Tests\GraphQL\Spiral\Fixture\AbstractClassForListeners;
use Andi\Tests\GraphQL\Spiral\Fixture\ObjectType;
use Andi\Tests\GraphQL\Spiral\Fixture\TraitForListeners;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Spiral\Core\Container;
use Spiral\Exceptions\ExceptionReporterInterface;
use Spiral\Tokenizer\TokenizationListenerInterface;

#[CoversClass(TypeLoaderListener::class)]
final class TypeLoaderListenerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private TypeLoaderListener $listener;

    private TypeRegistryInterface $typeRegistry;

    private ExceptionReporterInterface|LegacyMockInterface|MockInterface $reporter;

    protected function setUp(): void
    {
        $container = new Container();

        $this->typeRegistry = new TypeRegistry();
        $typeResolver = new TypeResolver();
        $typeResolver->pipe(new WebonyxGraphQLTypeMiddleware($container));

        $this->listener = new TypeLoaderListener(
            $this->typeRegistry,
            $typeResolver,
            $this->reporter = \Mockery::mock(ExceptionReporterInterface::class),
        );
    }

    public function testInstanceOf(): void
    {
        self::assertInstanceOf(TokenizationListenerInterface::class, $this->listener);
    }

    public function testIgnoreDuplicate(): void
    {
        $this->typeRegistry->register(new ObjectType(), ObjectType::class);

        $typeResolver = \Mockery::mock(TypeResolverInterface::class);
        $typeResolver->shouldReceive('resolve')->never();
        $this->reporter->shouldReceive('report')->never();

        $listener = new TypeLoaderListener(
            $this->typeRegistry,
            $typeResolver,
            $this->reporter,
        );

        $listener->listen(new \ReflectionClass(ObjectType::class));
        $listener->finalize();
    }

    #[DataProvider('getData')]
    public function testListener(array $expected, \ReflectionClass $reflection): void
    {
        if ($expected['suppressException'] ?? false) {
            $this->reporter->shouldReceive('report')->once();
        } else {
            $this->reporter->shouldReceive('report')->never();
        }

        $this->listener->listen($reflection);
        $this->listener->finalize();

        self::assertSame($expected['has'] ?? true, $this->typeRegistry->has($expected['name']));
    }

    public static function getData(): iterable
    {
        yield 'foo ObjectType' => [
            'expected' => [
                'name' => 'foo',
            ],
            'reflection' => new \ReflectionClass(ObjectType::class),
        ];

        yield 'ignore abstract class' => [
            'expected' => [
                'name' => 'foo',
                'has' => false,
            ],
            'reflection' => new \ReflectionClass(AbstractClassForListeners::class),
        ];

        yield 'ignore trait' => [
            'expected' => [
                'name' => 'foo',
                'has' => false,
            ],
            'reflection' => new \ReflectionClass(TraitForListeners::class),
        ];

        yield 'suppress exception (exclude middleware in test)' => [
            'expected' => [
                'name' => 'Query',
                'has' => false,
                'suppressException' => true, // exclude GraphQLTypeMiddleware
            ],
            'reflection' => new \ReflectionClass(QueryType::class),
        ];
    }
}
