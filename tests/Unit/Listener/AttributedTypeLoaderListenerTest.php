<?php

declare(strict_types=1);

namespace Andi\Tests\GraphQL\Spiral\Unit\Listener;

use Andi\GraphQL\Attribute\InputObjectType;
use Andi\GraphQL\Attribute\ObjectType;
use Andi\GraphQL\Exception\CantResolveGraphQLTypeException;
use Andi\GraphQL\InputObjectFieldResolver\InputObjectFieldResolverInterface;
use Andi\GraphQL\ObjectFieldResolver\ObjectFieldResolverInterface;
use Andi\GraphQL\Spiral\Listener\AttributedTypeLoaderListener;
use Andi\GraphQL\TypeRegistry;
use Andi\GraphQL\TypeRegistryInterface;
use Andi\GraphQL\TypeResolver\Middleware\AttributedGraphQLTypeMiddleware;
use Andi\GraphQL\TypeResolver\Middleware\EnumTypeMiddleware;
use Andi\GraphQL\TypeResolver\TypeResolver;
use Andi\Tests\GraphQL\Spiral\Fixture\AbstractClassForListeners;
use Andi\Tests\GraphQL\Spiral\Fixture\EnumForListeners;
use Andi\Tests\GraphQL\Spiral\Fixture\InterfaceForListeners;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Spiral\Attributes\Internal\NativeAttributeReader;
use Spiral\Core\Container;
use Spiral\Tokenizer\TokenizationListenerInterface;

#[CoversClass(AttributedTypeLoaderListener::class)]
#[UsesClass(TypeRegistry::class)]
#[UsesClass(TypeResolver::class)]
final class AttributedTypeLoaderListenerTest extends TestCase
{
    private AttributedTypeLoaderListener $listener;
    private TypeRegistryInterface $typeRegistry;

    protected function setUp(): void
    {
        $container = new Container();
        $reader = new NativeAttributeReader();

        $typeRegistry = $this->typeRegistry = new TypeRegistry();

        $container->bindSingleton(TypeRegistryInterface::class, static fn () => $typeRegistry);

        $typeResolver = new TypeResolver();
        $typeResolver->pipe(new EnumTypeMiddleware($reader));
        $typeResolver->pipe(new AttributedGraphQLTypeMiddleware(
            $container,
            $reader,
            $this->typeRegistry,
            \Mockery::mock(ObjectFieldResolverInterface::class),
            \Mockery::mock(InputObjectFieldResolverInterface::class),
            $container,
        ));

        $this->listener = new AttributedTypeLoaderListener(
            $this->typeRegistry,
            $typeResolver,
            $reader,
        );
    }

    public function testInstanceOf(): void
    {
        self::assertInstanceOf(TokenizationListenerInterface::class, $this->listener);
    }

    public function testSuppressException(): void
    {
        $typeResolver = new TypeResolver();
        $listener = new AttributedTypeLoaderListener(
            $this->typeRegistry,
            $typeResolver,
            new NativeAttributeReader(),
        );

        $reflection = new \ReflectionClass(new #[ObjectType(name: 'SimpleObjectType')] class {});

        // hasn't exception
        $listener->listen($reflection);
        $listener->finalize();

        $this->expectException(CantResolveGraphQLTypeException::class);
        $typeResolver->resolve($reflection);
    }

    #[DataProvider('getData')]
    public function testListener(array $expected, \ReflectionClass $reflection): void
    {
        $this->listener->listen($reflection);
        $this->listener->finalize();

        $has = $this->typeRegistry->has($expected['name']);

        self::assertSame($expected['has'] ?? false, $has);

        if ($has) {
            self::assertSame($expected['name'], (string) $this->typeRegistry->get($expected['name']));
        }
    }

    public static function getData(): iterable
    {
        yield 'SimpleObjectType' => [
            'expected' => [
                'name' => 'SimpleObjectType',
                'has' => true,
            ],
            'reflection' => new \ReflectionClass(new #[ObjectType(name: 'SimpleObjectType')] class {}),
        ];

        yield 'SimpleInputObjectType' => [
            'expected' => [
                'name' => 'SimpleInputObjectType',
                'has' => true,
            ],
            'reflection' => new \ReflectionClass(new #[InputObjectType(name: 'SimpleInputObjectType')] class {}),
        ];

        yield 'InterfaceForListeners' => [
            'expected' => [
                'name' => 'InterfaceForListeners',
                'has' => true,
            ],
            'reflection' => new \ReflectionClass(InterfaceForListeners::class),
        ];

        yield 'EnumForListeners' => [
            'expected' => [
                'name' => 'EnumForListeners',
                'has' => true,
            ],
            'reflection' => new \ReflectionEnum(EnumForListeners::class),
        ];

        yield 'ignored abstract class' => [
            'expected' => [
                'name' => 'AbstractClassForListeners',
            ],
            'reflection' => new \ReflectionClass(AbstractClassForListeners::class),
        ];

        yield 'ignored class with AbstractType attribute' => [
            'expected' => [
                'name' => 'IgnoredType',
            ],
            'reflection' => new \ReflectionClass(new class {}),
        ];
    }
}
