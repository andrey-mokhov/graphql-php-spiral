<?php

declare(strict_types=1);

namespace Andi\Tests\GraphQL\Spiral\Unit\Listener;

use Andi\GraphQL\ArgumentResolver\ArgumentResolver;
use Andi\GraphQL\ArgumentResolver\Middleware\ReflectionParameterMiddleware;
use Andi\GraphQL\Attribute\AdditionalField;
use Andi\GraphQL\ObjectFieldResolver\Middleware\AdditionalFieldByReflectionMethodMiddleware;
use Andi\GraphQL\ObjectFieldResolver\ObjectFieldResolver;
use Andi\GraphQL\Spiral\Listener\AbstractAdditionalFieldListener;
use Andi\GraphQL\Spiral\Listener\AdditionalFieldListener;
use Andi\GraphQL\TypeRegistry;
use Andi\GraphQL\TypeRegistryInterface;
use Andi\GraphQL\WebonyxType\ObjectType;
use Andi\Tests\GraphQL\Spiral\Fixture\AbstractClassForListeners;
use Andi\Tests\GraphQL\Spiral\Fixture\EnumForListeners;
use Andi\Tests\GraphQL\Spiral\Fixture\TraitForListeners;
use GraphQL\Type\Definition as Webonyx;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Spiral\Attributes\Internal\NativeAttributeReader;
use Spiral\Core\Container;
use Spiral\Tokenizer\TokenizationListenerInterface;

#[CoversClass(AdditionalFieldListener::class)]
#[CoversClass(AbstractAdditionalFieldListener::class)]
final class AdditionalFieldListenerTest extends TestCase
{
    private AdditionalFieldListener $listener;
    private TypeRegistryInterface $typeRegistry;

    protected function setUp(): void
    {
        $this->listener = new AdditionalFieldListener(
            $reader = new NativeAttributeReader(),
            $this->typeRegistry = new TypeRegistry(),
        );

        $objectFieldResolver = new ObjectFieldResolver();

        $argumentResolver = new ArgumentResolver();
        $argumentResolver->pipe(new ReflectionParameterMiddleware($reader, $this->typeRegistry));

        $container = new Container();
        $middleware = new AdditionalFieldByReflectionMethodMiddleware(
            $reader,
            $this->typeRegistry,
            $argumentResolver,
            $container,
            $container,
        );

        $objectFieldResolver->pipe($middleware);

        $this->typeRegistry->register(new ObjectType(['name' => 'Query'], $objectFieldResolver));
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
        $query = $this->typeRegistry->get('Query');

        $has = $query->hasField($expected['name']);

        self::assertSame($expected['has'] ?? false, $has);

        if ($has) {
            $field = $query->getField($expected['name']);
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
                #[AdditionalField(targetType: 'Query')]
                public function getFoo(): string
                {
                    return 'bar';
                }
            }),
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

        yield 'ignore trait' => [
            'expected' => [
                'name' => 'foo',
                'has' => false,
            ],
            'reflection' => new \ReflectionClass(TraitForListeners::class),
        ];

        yield 'ignore class with method in abstract class' => [
            'expected' => [
                'name' => 'foo',
                'has' => false,
            ],
            'reflection' => new \ReflectionClass(new class extends AbstractClassForListeners {}),
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
