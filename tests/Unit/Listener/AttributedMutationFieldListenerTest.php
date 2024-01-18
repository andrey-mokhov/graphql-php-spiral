<?php

declare(strict_types=1);

namespace Andi\Tests\GraphQL\Spiral\Unit\Listener;

use Andi\GraphQL\ArgumentResolver\ArgumentResolver;
use Andi\GraphQL\ArgumentResolver\Middleware\ReflectionParameterMiddleware;
use Andi\GraphQL\Attribute\MutationField;
use Andi\GraphQL\ObjectFieldResolver\Middleware\MutationFieldByReflectionMethodMiddleware;
use Andi\GraphQL\ObjectFieldResolver\ObjectFieldResolver;
use Andi\GraphQL\Spiral\Config\GraphQLConfig;
use Andi\GraphQL\Spiral\Listener\AbstractAdditionalFieldListener;
use Andi\GraphQL\Spiral\Listener\AttributedMutationFieldListener;
use Andi\GraphQL\TypeRegistry;
use Andi\GraphQL\TypeRegistryInterface;
use Andi\GraphQL\WebonyxType\ObjectType;
use GraphQL\Type\Definition as Webonyx;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Spiral\Attributes\Internal\NativeAttributeReader;
use Spiral\Core\Container;
use Spiral\Tokenizer\TokenizationListenerInterface;

#[CoversClass(AttributedMutationFieldListener::class)]
#[UsesClass(AbstractAdditionalFieldListener::class)]
#[UsesClass(GraphQLConfig::class)]
final class AttributedMutationFieldListenerTest extends TestCase
{
    private AttributedMutationFieldListener $listener;
    private TypeRegistryInterface $typeRegistry;

    protected function setUp(): void
    {
        $this->listener = new AttributedMutationFieldListener(
            $reader = new NativeAttributeReader(),
            $this->typeRegistry = new TypeRegistry(),
            new GraphQLConfig(['mutationType' => 'Mutation']),
        );

        $objectFieldResolver = new ObjectFieldResolver();

        $argumentResolver = new ArgumentResolver();
        $argumentResolver->pipe(new ReflectionParameterMiddleware($reader, $this->typeRegistry));

        $container = new Container();
        $middleware = new MutationFieldByReflectionMethodMiddleware(
            $reader,
            $this->typeRegistry,
            $argumentResolver,
            $container,
            $container,
        );

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

        /** @var Webonyx\ObjectType $mutation */
        $mutation = $this->typeRegistry->get('Mutation');

        $has = $mutation->hasField($expected['name']);

        self::assertSame($expected['has'] ?? false, $has);

        if ($has) {
            $field = $mutation->getField($expected['name']);
        } else {
            return;
        }

        if (isset($expected['type'])) {
            self::assertSame($expected['type'], (string) $field->getType());
        }
    }

    public static function getData(): iterable
    {
        yield 'Foo field' => [
            'expected' => [
                'name' => 'foo',
                'has' => true,
                'type' => 'String!',
            ],
            'reflection' => new \ReflectionClass(new class {
                #[MutationField]
                public function getFoo(): string
                {
                    return 'bar';
                }
            }),
        ];

        yield 'ignore class without attribute' => [
            'expected' => [
                'name' => 'foo',
                'has' => false,
            ],
            'reflection' => new \ReflectionClass(new class {
                public function getFoo(): string
                {
                    return 'bar';
                }
            }),
        ];
    }
}
