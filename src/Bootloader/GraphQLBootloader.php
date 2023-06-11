<?php

declare(strict_types=1);

namespace Andi\GraphQL\Spiral\Bootloader;

use Andi\GraphQL\ArgumentResolver\ArgumentResolver;
use Andi\GraphQL\ArgumentResolver\ArgumentResolverInterface;
use Andi\GraphQL\InputObjectFieldResolver\InputObjectFieldResolver;
use Andi\GraphQL\InputObjectFieldResolver\InputObjectFieldResolverInterface;
use Andi\GraphQL\ObjectFieldResolver\ObjectFieldResolver;
use Andi\GraphQL\ObjectFieldResolver\ObjectFieldResolverInterface;
use Andi\GraphQL\Spiral\Config\GraphQLConfig;
use Andi\GraphQL\Spiral\Listener\MutationFieldListener;
use Andi\GraphQL\Spiral\Listener\QueryFieldListener;
use Andi\GraphQL\Spiral\Listener\TypeLoaderListener;
use Andi\GraphQL\Spiral\Middleware\GraphQLMiddleware;
use Andi\GraphQL\Type\MutationType;
use Andi\GraphQL\Type\QueryType;
use Andi\GraphQL\TypeRegistry;
use Andi\GraphQL\TypeRegistryInterface;
use Andi\GraphQL\TypeResolver\TypeResolver;
use Andi\GraphQL\TypeResolver\TypeResolverInterface;
use App\Application\Bootloader\RoutesBootloader;
use GraphQL\Server\ServerConfig;
use GraphQL\Server\StandardServer;
use GraphQL\Type\Schema;
use GraphQL\Type\SchemaConfig;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Boot\EnvironmentInterface;
use Spiral\Bootloader\Http\HttpBootloader;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Tokenizer\TokenizerListenerRegistryInterface;

final class GraphQLBootloader extends Bootloader
{
    protected const SINGLETONS = [
        StandardServer::class        => [self::class, 'buildStandardServer'],
        ServerConfig::class          => [self::class, 'buildServerConfig'],
        Schema::class                => [self::class, 'buildSchema'],
        SchemaConfig::class          => [self::class, 'buildSchemaConfig'],

        TypeRegistryInterface::class => TypeRegistry::class,
        TypeRegistry::class          => [self::class, 'buildTypeRegistry'],

        TypeResolverInterface::class             => TypeResolver::class,
        ObjectFieldResolverInterface::class      => ObjectFieldResolver::class,
        InputObjectFieldResolverInterface::class => InputObjectFieldResolver::class,
        ArgumentResolverInterface::class         => ArgumentResolver::class,

        TypeResolver::class             => [self::class, 'buildTypeResolver'],
        ObjectFieldResolver::class      => [self::class, 'buildObjectFieldResolver'],
        InputObjectFieldResolver::class => [self::class, 'buildInputObjectFieldResolver'],
        ArgumentResolver::class         => [self::class, 'buildArgumentResolver'],
    ];

    protected const DEPENDENCIES = [
        RoutesBootloader::class,
    ];

    public function __construct(
        private readonly ConfiguratorInterface $configurator,
    ) {
    }

    public function init(
        EnvironmentInterface $env,
        HttpBootloader $bootloader,
    ): void {
        $this->configurator->setDefaults(GraphQLConfig::CONFIG, [
            'url' => $env->get('GRAPHQL_URL', '/api/graphql'),
        ]);

        $bootloader->addMiddleware(GraphQLMiddleware::class);
    }

    public function boot(
        GraphQLConfig $config,
        TypeRegistryInterface $typeRegistry,
        TypeResolverInterface $typeResolver,
        TokenizerListenerRegistryInterface $listenerRegistry,
        TypeLoaderListener $typeLoaderListener,
        QueryFieldListener $queryFieldListener,
        MutationFieldListener $mutationFieldListener,
    ): void {
        $this->registerQueryType($config->getQueryType(), $typeRegistry, $typeResolver);
        $this->registerMutationType($config->getMutationType(), $typeRegistry, $typeResolver);

        $listenerRegistry->addListener($typeLoaderListener);
        $listenerRegistry->addListener($queryFieldListener);
        $listenerRegistry->addListener($mutationFieldListener);
    }

    private function registerQueryType(
        string $class,
        TypeRegistryInterface $typeRegistry,
        TypeResolverInterface $typeResolver,
    ): void {
        if ($typeRegistry->has($class)) {
            return;
        }

        if (GraphQLConfig::DEFAULT_QUERY_TYPE === $class) {
            $class = QueryType::class;
        }

        $queryType = $typeResolver->resolve($class);
        $typeRegistry->register($queryType, $class);
    }

    private function registerMutationType(
        ?string $class,
        TypeRegistryInterface $typeRegistry,
        TypeResolverInterface $typeResolver,
    ): void {
        if (null === $class || $typeRegistry->has($class)) {
            return;
        }

        if (GraphQLConfig::DEFAULT_MUTATION_TYPE === $class) {
            $class = MutationType::class;
        }

        $queryType = $typeResolver->resolve($class);
        $typeRegistry->register($queryType, $class);
    }

    private function buildStandardServer(ServerConfig $config): StandardServer
    {
        return new StandardServer($config);
    }

    private function buildServerConfig(Schema $schema): ServerConfig
    {
        $config = (new ServerConfig())
            ->setSchema($schema);

        $config->setDebugFlag();

        return $config;
    }

    private function buildSchema(SchemaConfig $config): Schema
    {
        return new Schema($config);
    }

    private function buildSchemaConfig(TypeRegistryInterface $typeRegistry): SchemaConfig
    {
        $schemaConfig = new SchemaConfig();
        $schemaConfig->setTypeLoader($typeRegistry);
        $schemaConfig->setTypes($typeRegistry->getTypes(...));

        $schemaConfig->setQuery($typeRegistry->get('Query'));

        if ($typeRegistry->has('Mutation')) {
            $schemaConfig->setMutation($typeRegistry->get('Mutation'));
        }

        return $schemaConfig;
    }

    private function buildTypeRegistry(): TypeRegistry
    {
        return new TypeRegistry();
    }

    private function buildTypeResolver(
        ContainerInterface $container,
        GraphQLConfig $config,
    ): TypeResolver {
        $typeResolver = new TypeResolver();

        foreach ($config->getTypeResolverMiddlewares() as $name => $priority) {
            $typeResolver->pipe($container->get($name), $priority);
        }

        return $typeResolver;
    }

    private function buildObjectFieldResolver(
        ContainerInterface $container,
        GraphQLConfig $config,
    ): ObjectFieldResolver {
        $objectFieldResolver = new ObjectFieldResolver();

        foreach ($config->getObjectFieldResolverMiddlewares() as $name => $priority) {
            $objectFieldResolver->pipe($container->get($name), $priority);
        }

        return $objectFieldResolver;
    }

    private function buildInputObjectFieldResolver(
        ContainerInterface $container,
        GraphQLConfig $config,
    ): InputObjectFieldResolver {
        $inputObjectFieldResolver = new InputObjectFieldResolver();

        foreach ($config->getInputObjectFieldResolverMiddlewares() as $name => $priority) {
            $inputObjectFieldResolver->pipe($container->get($name), $priority);
        }

        return $inputObjectFieldResolver;
    }

    private function buildArgumentResolver(
        ContainerInterface $container,
        GraphQLConfig $config,
    ): ArgumentResolver {
        $argumentResolver = new ArgumentResolver();

        foreach ($config->getArgumentResolverMiddlewares() as $name => $priority) {
            $argumentResolver->pipe($container->get($name), $priority);
        }

        return $argumentResolver;
    }
}
