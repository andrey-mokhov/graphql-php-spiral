<?php

declare(strict_types=1);

namespace Andi\Tests\GraphQL\Spiral\Feature;

use Andi\GraphQL\Spiral\Bootloader\GraphQLBootloader;
use Andi\GraphQL\Spiral\Common\SchemaWarmupper;
use Andi\GraphQL\Spiral\Config\GraphQLConfig;
use Andi\GraphQL\Spiral\Listener\AbstractAdditionalFieldListener;
use Andi\GraphQL\Spiral\Listener\AdditionalFieldListener;
use Andi\GraphQL\Spiral\Listener\AttributedMutationFieldListener;
use Andi\GraphQL\Spiral\Listener\AttributedQueryFieldListener;
use Andi\GraphQL\Spiral\Listener\AttributedTypeLoaderListener;
use Andi\GraphQL\Spiral\Listener\MutationFieldListener;
use Andi\GraphQL\Spiral\Listener\QueryFieldListener;
use Andi\GraphQL\Spiral\Listener\TypeLoaderListener;
use Andi\GraphQL\Spiral\Middleware\GraphQLMiddleware;
use Andi\GraphQL\TypeRegistryInterface;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Spiral\Boot\Bootloader\CoreBootloader;
use Spiral\Boot\Memory;
use Spiral\Boot\MemoryInterface;
use Spiral\Bootloader\Attributes\AttributesBootloader;
use Spiral\Bootloader\Http\HttpBootloader;
use Spiral\Bootloader\Http\RouterBootloader;
use Spiral\Core\Container;
use Spiral\Testing\Attribute\Config;
use Spiral\Testing\TestableKernelInterface;
use Spiral\Testing\TestCase;
use Spiral\Tokenizer\Bootloader\TokenizerBootloader;
use Spiral\Tokenizer\Bootloader\TokenizerListenerBootloader;

#[CoversClass(SchemaWarmupper::class)]
#[UsesClass(GraphQLBootloader::class)]
#[UsesClass(GraphQLConfig::class)]
#[UsesClass(AbstractAdditionalFieldListener::class)]
#[UsesClass(AdditionalFieldListener::class)]
#[UsesClass(AttributedMutationFieldListener::class)]
#[UsesClass(AttributedQueryFieldListener::class)]
#[UsesClass(AttributedTypeLoaderListener::class)]
#[UsesClass(MutationFieldListener::class)]
#[UsesClass(QueryFieldListener::class)]
#[UsesClass(TypeLoaderListener::class)]
#[UsesClass(GraphQLMiddleware::class)]
final class GraphQLTest extends TestCase
{
    public function rootDirectory(): string
    {
        return dirname(__DIR__) . '/dirs';
    }

    public function createAppInstance(Container $container = new Container()): TestableKernelInterface
    {
        $container->bindSingleton(Memory::class, MemoryInterface::class);
        $container->bindSingleton(UriFactoryInterface::class, Psr17Factory::class);
        $container->bindSingleton(ResponseFactoryInterface::class, Psr17Factory::class);
        $container->bindSingleton(StreamFactoryInterface::class, Psr17Factory::class);

        return parent::createAppInstance($container);
    }

    public function defineBootloaders(): array
    {
        return [
            CoreBootloader::class,
            AttributesBootloader::class,
            TokenizerBootloader::class,
            TokenizerListenerBootloader::class,
            HttpBootloader::class,
            RouterBootloader::class,
            GraphQLBootloader::class,
        ];
    }

    #[Config('graphql.mutationType', 'Mutation')]
    #[DataProvider('getData')]
    public function testGraphQL(array $expected, array $data): void
    {
        $config = $this->getContainer()->get(GraphQLConfig::class);
        $typeRegistry = $this->getContainer()->get(TypeRegistryInterface::class);
        $response = $this->fakeHttp()->postJson($config->getUrl(), $data);

        $response->assertStatus(200);
        $result = $response->getJsonParsedBody();

        self::assertEquals($expected, $result, \json_encode($result));
    }

    public static function getData(): iterable
    {
        yield 'universal request' => [
            'expected' => [
                'data' => [
                    '__typename' => 'Query',
                ],
            ],
            'data' => [
                'operationName' => null,
                'variables' => new \stdClass(),
                'query' => <<<'QUERY'
                    {__typename}
                    QUERY,
            ],
        ];

        yield 'query echo' => [
            'expected' => [
                'data' => [
                    'queryEcho' => 'echo: hello',
                ],
            ],
            'data' => [
                'operationName' => null,
                'variables' => new \stdClass(),
                'query' => <<<'QUERY'
                    {queryEcho(message:"hello")}
                    QUERY,
            ],
        ];

        yield 'mutation echo' => [
            'expected' => [
                'data' => [
                    'tmp' => 'echo: foo',
                ],
            ],
            'data' => [
                'operationName' => null,
                'variables' => ['mess' => 'foo'],
                'query' => <<<'QUERY'
                    mutation tst($mess: String!) {tmp: mutationEcho(message:$mess)}
                    QUERY,
            ],
        ];

        yield 'ObjectType via InterfaceType' => [
            'expected' => [
                'data' => [
                    'user' => [
                        'lastname' => 'Gagarin',
                        'firstname' => 'Yuri',
                    ],
                ],
            ],
            'data' => [
                'operationName' => null,
                'variables' => new \stdClass(),
                'query' => <<<'QUERY'
                    {
                        user {
                            ... on User {
                                lastname
                                firstname
                            }
                        }
                    }
                    QUERY,
            ],
        ];

        yield 'enum example' => [
            'expected' => [
                'data' => [
                    'inverseDirection' => 'desc',
                ],
            ],
            'data' => [
                'operationName' => null,
                'variables' => new \stdClass(),
                'query' => <<<'QUERY'
                    {inverseDirection(direction: asc)}
                    QUERY,
            ],
        ];

        yield 'InputObjectType example' => [
            'expected' => [
                'data' => [
                    'registration' => [
                        'lastname' => 'foo',
                        'firstname' => 'bar',
                    ],
                ],
            ],
            'data' => [
                'operationName' => null,
                'variables' => new \stdClass(),
                'query' => <<<'QUERY'
                    mutation {
                        registration(input: {lastname:"foo" firstname: "bar"}) {
                            lastname
                            firstname
                        }
                    }
                    QUERY,
            ],
        ];

        yield 'autogenerated union-type' => [
            'expected' => [
                'data' => [
                    'everyone' => [
                        'name' => 'Apple',
                    ],
                ],
            ],
            'data' => [
                'operationName' => null,
                'variables' => new \stdClass(),
                'query' => <<<'QUERY'
                    {
                        everyone {
                            ... on Company {
                                name
                            }
                            ... on User {
                                lastname
                                firstname
                            }
                        }
                    }
                    QUERY,
            ],
        ];
    }
}
