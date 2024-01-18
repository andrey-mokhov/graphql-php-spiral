<?php

declare(strict_types=1);

namespace Andi\Tests\GraphQL\Spiral\Feature\Bootloader;

use Andi\GraphQL\ArgumentResolver\ArgumentResolver;
use Andi\GraphQL\ArgumentResolver\ArgumentResolverInterface;
use Andi\GraphQL\InputObjectFieldResolver\InputObjectFieldResolver;
use Andi\GraphQL\InputObjectFieldResolver\InputObjectFieldResolverInterface;
use Andi\GraphQL\ObjectFieldResolver\ObjectFieldResolver;
use Andi\GraphQL\ObjectFieldResolver\ObjectFieldResolverInterface;
use Andi\GraphQL\Spiral\Bootloader\GraphQLBootloader;
use Andi\GraphQL\Spiral\Command\ConfigCommand;
use Andi\GraphQL\Spiral\Common\SchemaWarmupper;
use Andi\GraphQL\Spiral\Common\ValueResolver;
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
use Andi\GraphQL\TypeRegistry;
use Andi\GraphQL\TypeRegistryInterface;
use Andi\GraphQL\TypeResolver\TypeResolver;
use Andi\GraphQL\TypeResolver\TypeResolverInterface;
use Andi\Tests\GraphQL\Spiral\dirs\app\src\Common\ContextResolver;
use Andi\Tests\GraphQL\Spiral\dirs\app\src\Common\RootValueResolver;
use GraphQL\Language\AST\DocumentNode;
use GraphQL\Server\OperationParams;
use GraphQL\Server\ServerConfig;
use GraphQL\Server\StandardServer;
use GraphQL\Type\Definition as Webonyx;
use GraphQL\Type\Schema;
use GraphQL\Type\SchemaConfig;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Spiral\Boot\Bootloader\CoreBootloader;
use Spiral\Boot\EnvironmentInterface;
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

#[CoversClass(GraphQLBootloader::class)]
#[UsesClass(ConfigCommand::class)]
#[UsesClass(SchemaWarmupper::class)]
#[UsesClass(GraphQLConfig::class)]
#[UsesClass(AbstractAdditionalFieldListener::class)]
#[UsesClass(AdditionalFieldListener::class)]
#[UsesClass(AttributedQueryFieldListener::class)]
#[UsesClass(AttributedMutationFieldListener::class)]
#[UsesClass(AttributedTypeLoaderListener::class)]
#[UsesClass(QueryFieldListener::class)]
#[UsesClass(MutationFieldListener::class)]
#[UsesClass(TypeLoaderListener::class)]
#[UsesClass(TypeRegistry::class)]
#[UsesClass(ValueResolver::class)]
final class GraphQLBootloaderTest extends TestCase
{
    public function rootDirectory(): string
    {
        return dirname(__DIR__, 2) . '/dirs';
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

    public function testExistsConsoleCommand(): void
    {
        $this->assertCommandRegistered('graphql:config');
    }

    public function testDefaultServicesWithDefaultConfig()
    {
        $container = $this->getContainer();

        self::assertTrue($container->has(StandardServer::class));
        self::assertInstanceOf(StandardServer::class, $container->get(StandardServer::class));

        self::assertTrue($container->has(ServerConfig::class));
        self::assertInstanceOf(ServerConfig::class, $container->get(ServerConfig::class));

        self::assertTrue($container->has(Schema::class));
        self::assertInstanceOf(Schema::class, $container->get(Schema::class));

        self::assertTrue($container->has(SchemaConfig::class));
        self::assertInstanceOf(SchemaConfig::class, $container->get(SchemaConfig::class));

        self::assertTrue($container->has(TypeRegistryInterface::class));
        self::assertTrue($container->has(TypeRegistry::class));
        self::assertInstanceOf(TypeRegistryInterface::class, $container->get(TypeRegistryInterface::class));
        self::assertInstanceOf(TypeRegistryInterface::class, $container->get(TypeRegistry::class));

        self::assertTrue($container->has(TypeResolverInterface::class));
        self::assertTrue($container->has(TypeResolver::class));
        self::assertInstanceOf(TypeResolverInterface::class, $container->get(TypeResolverInterface::class));
        self::assertInstanceOf(TypeResolverInterface::class, $container->get(TypeResolver::class));

        self::assertTrue($container->has(ObjectFieldResolverInterface::class));
        self::assertTrue($container->has(ObjectFieldResolver::class));
        self::assertInstanceOf(
            ObjectFieldResolverInterface::class,
            $container->get(ObjectFieldResolverInterface::class),
        );
        self::assertInstanceOf(ObjectFieldResolverInterface::class, $container->get(ObjectFieldResolver::class));

        self::assertTrue($container->has(InputObjectFieldResolverInterface::class));
        self::assertTrue($container->has(InputObjectFieldResolver::class));
        self::assertInstanceOf(
            InputObjectFieldResolverInterface::class,
            $container->get(InputObjectFieldResolverInterface::class),
        );
        self::assertInstanceOf(
            InputObjectFieldResolverInterface::class,
            $container->get(InputObjectFieldResolver::class),
        );

        self::assertTrue($container->has(ArgumentResolverInterface::class));
        self::assertTrue($container->has(ArgumentResolver::class));
        self::assertInstanceOf(
            ArgumentResolverInterface::class,
            $container->get(ArgumentResolverInterface::class),
        );
        self::assertInstanceOf(
            ArgumentResolverInterface::class,
            $container->get(ArgumentResolver::class),
        );
    }

    public function testStandardTypeWithDefaultConfig(): void
    {
        /** @var Schema $schema */
        $schema = $this->getContainer()->get(Schema::class);

        $query = $schema->getQueryType();
        self::assertInstanceOf(Webonyx\ObjectType::class, $query);
        self::assertSame('Query', (string) $query);

        self::assertNull($schema->getMutationType());
    }

    #[Config('graphql.mutationType', GraphQLConfig::DEFAULT_MUTATION_TYPE)]
    public function testMutationViaConfig(): void
    {
        /** @var Schema $schema */
        $schema = $this->getContainer()->get(Schema::class);

        $mutation = $schema->getMutationType();
        self::assertInstanceOf(Webonyx\ObjectType::class, $mutation);
        self::assertSame('Mutation', (string) $mutation);
    }

    #[Config('graphql.additionalTypes', [DateTime::class])]
    public function testAdditionalTypesRegistrationViaConfig(): void
    {
        $typeRegistry = $this->getContainer()->get(TypeRegistryInterface::class);

        self::assertTrue($typeRegistry->has(DateTime::class));
        self::assertTrue($typeRegistry->has('DateTime'));

        $dateTime = $typeRegistry->get(DateTime::class);

        self::assertInstanceOf(Webonyx\ScalarType::class, $dateTime);
        self::assertSame('DateTime', (string) $dateTime);
    }

    #[Config('graphql.rootValue', EnvironmentInterface::class)]
    #[Config('graphql.context', ContainerInterface::class)]
    public function testServerConfigViaConfig(): void
    {
        /** @var ServerConfig $serverConfig */
        $serverConfig = $this->getContainer()->get(ServerConfig::class);

        $rootValueFn = $serverConfig->getRootValue();
        self::assertIsCallable($rootValueFn);

        $rootValue = $rootValueFn(new OperationParams(), new DocumentNode([]), 'query');
        self::assertInstanceOf(EnvironmentInterface::class, $rootValue);

        $contextFn = $serverConfig->getContext();
        self::assertIsCallable($contextFn);

        $context = $contextFn(new OperationParams(), new DocumentNode([]), 'query');
        self::assertInstanceOf(ContainerInterface::class, $context);
    }

    #[Config('graphql.rootValue', RootValueResolver::class)]
    #[Config('graphql.context', ContextResolver::class)]
    public function testServerConfigViaConfigWithCallable(): void
    {
        /** @var ServerConfig $serverConfig */
        $serverConfig = $this->getContainer()->get(ServerConfig::class);

        $rootValueFn = $serverConfig->getRootValue();
        self::assertIsCallable($rootValueFn);

        $rootValue = $rootValueFn(new OperationParams(), new DocumentNode([]), 'query');
        self::assertSame('rootValue', $rootValue);

        $contextFn = $serverConfig->getContext();
        self::assertIsCallable($contextFn);

        $context = $contextFn(new OperationParams(), new DocumentNode([]), 'query');
        self::assertSame('context', $context);
    }
}
