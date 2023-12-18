<?php

declare(strict_types=1);

namespace Andi\Tests\GraphQL\Spiral\Feature\Config;

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
use Andi\GraphQL\TypeResolver\Middleware\AttributedGraphQLTypeMiddleware;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
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

    public function defineBootloaders(): array
    {
        return [
            GraphQLBootloader::class,
        ];
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
}
