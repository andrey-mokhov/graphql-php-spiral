<?php

declare(strict_types=1);

namespace Andi\Tests\GraphQL\Spiral\Unit\Listener;

use Andi\GraphQL\ArgumentResolver\ArgumentResolver;
use Andi\GraphQL\ArgumentResolver\Middleware\ReflectionParameterMiddleware;
use Andi\GraphQL\ObjectFieldResolver\Middleware\WebonyxObjectFieldMiddleware;
use Andi\GraphQL\ObjectFieldResolver\ObjectFieldResolver;
use Andi\GraphQL\Spiral\Config\GraphQLConfig;
use Andi\GraphQL\Spiral\Listener\MutationFieldListener;
use Andi\GraphQL\TypeRegistry;
use Andi\GraphQL\TypeRegistryInterface;
use Andi\GraphQL\WebonyxType\ObjectType;
use Andi\Tests\GraphQL\Spiral\Fixture\AbstractClassForListeners;
use Andi\Tests\GraphQL\Spiral\Fixture\EnumForListeners;
use Andi\Tests\GraphQL\Spiral\Fixture\InterfaceForListeners;
use Andi\Tests\GraphQL\Spiral\Fixture\ObjectField;
use GraphQL\Type\Definition as Webonyx;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Spiral\Attributes\Internal\NativeAttributeReader;
use Spiral\Core\Container;
use Spiral\Tokenizer\TokenizationListenerInterface;

#[CoversClass(MutationFieldListener::class)]
#[UsesClass(GraphQLConfig::class)]
final class MutationFieldListenerTest extends TestCase
{
    private MutationFieldListener $listener;
    private TypeRegistryInterface $typeRegistry;

    protected function setUp(): void
    {
        $container = new Container();
        $reader = new NativeAttributeReader();

        $this->listener = new MutationFieldListener(
            $container,
            $this->typeRegistry = new TypeRegistry(),
            new GraphQLConfig(['mutationType' => 'Mutation']),
        );

        $objectFieldResolver = new ObjectFieldResolver();

        $argumentResolver = new ArgumentResolver();
        $argumentResolver->pipe(new ReflectionParameterMiddleware($reader, $this->typeRegistry));

        $middleware = new WebonyxObjectFieldMiddleware();
        $objectFieldResolver->pipe($middleware);

        $this->typeRegistry->register(new ObjectType(['name' => 'Mutation'], $objectFieldResolver));
    }

    public function testInstanceOf(): void
    {
        self::assertInstanceOf(TokenizationListenerInterface::class, $this->listener);
    }

    #[DataProvider('getData')]
    public function testListener(array $expected, \ReflectionClass $reflection): void
    {
        $this->listener->listen($reflection);
        $this->listener->finalize();

        /** @var Webonyx\ObjectType $query */
        $query = $this->typeRegistry->get('Mutation');

        self::assertSame($expected['has'] ?? true, $query->hasField($expected['name']));
    }

    public static function getData(): iterable
    {
        yield 'foo' => [
            'expected' => [
                'name' => 'foo',
            ],
            'reflection' => new \ReflectionClass(ObjectField::class),
        ];

        yield 'ignore abstract class' => [
            'expected' => [
                'name' => 'foo',
                'has' => false,
            ],
            'reflection' => new \ReflectionClass(AbstractClassForListeners::class),
        ];

        yield 'ignore enum' => [
            'expected' => [
                'name' => 'foo',
                'has' => false,
            ],
            'reflection' => new \ReflectionEnum(EnumForListeners::class),
        ];

        yield 'ignore interface' => [
            'expected' => [
                'name' => 'foo',
                'has' => false,
            ],
            'reflection' => new \ReflectionClass(InterfaceForListeners::class),
        ];
    }
}
