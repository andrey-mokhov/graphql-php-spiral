<?php

declare(strict_types=1);

namespace Andi\Tests\GraphQL\Spiral\Feature\Config;

use Andi\GraphQL\ArgumentResolver\Middleware\ArgumentConfigurationMiddleware;
use Andi\GraphQL\ArgumentResolver\Middleware\ArgumentMiddleware;
use Andi\GraphQL\InputObjectFieldResolver\Middleware\InputObjectFieldMiddleware;
use Andi\GraphQL\InputObjectFieldResolver\Middleware\WebonyxInputObjectFieldMiddleware;
use Andi\GraphQL\ObjectFieldResolver\Middleware\ObjectFieldMiddleware;
use Andi\GraphQL\ObjectFieldResolver\Middleware\WebonyxObjectFieldMiddleware;
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
use Andi\GraphQL\Type\DateTime;
use Andi\GraphQL\Type\MutationType;
use Andi\GraphQL\Type\QueryType;
use Andi\GraphQL\TypeResolver\Middleware\AttributedGraphQLTypeMiddleware;
use Andi\GraphQL\TypeResolver\Middleware\GraphQLTypeMiddleware;
use Andi\GraphQL\TypeResolver\Middleware\WebonyxGraphQLTypeMiddleware;
use GraphQL\Error\DebugFlag;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Psr\Log\LoggerInterface;
use Spiral\Core\InjectableConfig;
use Spiral\Testing\Attribute\Config;
use Spiral\Testing\Attribute\Env;
use Spiral\Testing\TestCase;

#[CoversClass(GraphQLConfig::class)]
#[UsesClass(GraphQLBootloader::class)]
#[UsesClass(AdditionalFieldListener::class)]
#[UsesClass(AbstractAdditionalFieldListener::class)]
#[UsesClass(AttributedTypeLoaderListener::class)]
#[UsesClass(AttributedGraphQLTypeMiddleware::class)]
#[UsesClass(AttributedQueryFieldListener::class)]
#[UsesClass(AttributedMutationFieldListener::class)]
#[UsesClass(QueryFieldListener::class)]
#[UsesClass(MutationFieldListener::class)]
#[UsesClass(TypeLoaderListener::class)]
#[UsesClass(SchemaWarmupper::class)]
final class GraphQLConfigTest extends TestCase
{
    private GraphQLConfig $config;

    public function rootDirectory(): string
    {
        return dirname(__DIR__, 2) . '/dirs';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->config = $this->getContainer()->get(GraphQLConfig::class);
    }


    public function testInstanceOf(): void
    {
        self::assertInstanceOf(InjectableConfig::class, $this->config);
    }

    public function testSingleton(): void
    {
        $container = $this->getContainer();
        $first = $container->get(GraphQLConfig::class);
        $second = $container->get(GraphQLConfig::class);

        self::assertSame($second, $first);
    }

    public function testDefaultUrl(): void
    {
        self::assertSame('/api/graphql', $this->config->getUrl());
    }

    #[Env('GRAPHQL_URL', '/url-via-env')]
    public function testUrlViaEnvironment(): void
    {
        self::assertSame('/url-via-env', $this->config->getUrl());
    }

    #[Config('graphql.url', '/url-via-config')]
    public function testUrlViaConfig(): void
    {
        self::assertSame('/url-via-config', $this->config->getUrl());
    }

    public function testDefaultQueryType(): void
    {
        self::assertSame(GraphQLConfig::DEFAULT_QUERY_TYPE, $this->config->getQueryType());
    }

    #[Config('graphql.queryType', QueryType::class)]
    public function testQueryTypeViaConfig()
    {
        self::assertSame(QueryType::class, $this->config->getQueryType());
    }

    public function testDefaultMutationType(): void
    {
        self::assertNull($this->config->getMutationType());
    }

    #[Config('graphql.mutationType', MutationType::class)]
    public function testMutationTypeViaConfig(): void
    {
        self::assertSame(MutationType::class, $this->config->getMutationType());
    }

    public function testDefaultRootValue(): void
    {
        self::assertNull($this->config->getRootValue());
    }

    #[Config('graphql.rootValue', LoggerInterface::class)]
    public function testRootValueViaConfig(): void
    {
        self::assertSame(LoggerInterface::class, $this->config->getRootValue());
    }

    public function testDefaultContext(): void
    {
        self::assertNull($this->config->getContext());
    }

    #[Config('graphql.context', LoggerInterface::class)]
    public function testContextViaConfig(): void
    {
        self::assertSame(LoggerInterface::class, $this->config->getContext());
    }

    public function testDefaultDebugFlag(): void
    {
        self::assertSame(DebugFlag::INCLUDE_DEBUG_MESSAGE, $this->config->getDebugFlag());
    }

    #[Config('graphql.debugFlag', DebugFlag::NONE)]
    public function testDebugFlagViaConfig(): void
    {
        self::assertSame(DebugFlag::NONE, $this->config->getDebugFlag());
    }

    public function testDefaultTypeResolverMiddlewares(): void
    {
        self::assertArrayHasKey(WebonyxGraphQLTypeMiddleware::class, $this->config->getTypeResolverMiddlewares());
    }

    #[Config('graphql.typeResolverMiddlewares', [GraphQLTypeMiddleware::class => GraphQLTypeMiddleware::PRIORITY])]
    public function testTypeResolverMiddlewaresViaConfig(): void
    {
        self::assertArrayNotHasKey(WebonyxGraphQLTypeMiddleware::class, $this->config->getTypeResolverMiddlewares());
    }

    public function testDefaultObjectFieldResolverMiddlewares(): void
    {
        self::assertArrayHasKey(
            WebonyxObjectFieldMiddleware::class,
            $this->config->getObjectFieldResolverMiddlewares()
        );
    }

    #[Config(
        'graphql.objectFieldResolverMiddlewares',
        [ObjectFieldMiddleware::class => ObjectFieldMiddleware::PRIORITY],
    )]
    public function testObjectFieldResolverMiddlewaresViaConfig(): void
    {
        self::assertArrayNotHasKey(
            WebonyxObjectFieldMiddleware::class,
            $this->config->getObjectFieldResolverMiddlewares()
        );
    }

    public function testDefaultInputObjectFieldResolverMiddlewares(): void
    {
        self::assertArrayHasKey(
            WebonyxInputObjectFieldMiddleware::class,
            $this->config->getInputObjectFieldResolverMiddlewares(),
        );
    }

    #[Config(
        'graphql.inputObjectFieldResolverMiddlewares',
        [InputObjectFieldMiddleware::class => InputObjectFieldMiddleware::PRIORITY],
    )]
    public function testInputObjectFieldResolverMiddlewaresViaConfig(): void
    {
        self::assertArrayNotHasKey(
            WebonyxInputObjectFieldMiddleware::class,
            $this->config->getInputObjectFieldResolverMiddlewares(),
        );
    }

    public function testDefaultArgumentResolverMiddlewares(): void
    {
        self::assertArrayHasKey(
            ArgumentConfigurationMiddleware::class,
            $this->config->getArgumentResolverMiddlewares(),
        );
    }

    #[Config('graphql.argumentResolverMiddlewares', [ArgumentMiddleware::class => ArgumentMiddleware::PRIORITY])]
    public function testArgumentResolverMiddlewaresViaConfig(): void
    {
        self::assertArrayNotHasKey(
            ArgumentConfigurationMiddleware::class,
            $this->config->getArgumentResolverMiddlewares(),
        );
    }

    public function testDefaultAdditionalTypes(): void
    {
        self::assertEmpty($this->config->getAdditionalTypes());
    }

    #[Config('graphql.additionalTypes', [DateTime::class => \DateTimeImmutable::class])]
    public function testAdditionalTypesViaConfig(): void
    {
        self::assertArrayHasKey(DateTime::class, $this->config->getAdditionalTypes());
    }
}
