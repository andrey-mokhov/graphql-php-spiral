<?php

declare(strict_types=1);

namespace Andi\GraphQL\Spiral\Bootloader;

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
        StandardServer::class        => [self::class, 'makeStandardServer'],
        ServerConfig::class          => [self::class, 'makeServerConfig'],
        Schema::class                => [self::class, 'makeSchema'],
        SchemaConfig::class          => [self::class, 'makeSchemaConfig'],

        TypeRegistryInterface::class => TypeRegistry::class,
        TypeResolverInterface::class => TypeResolver::class,
        TypeRegistry::class          => [self::class, 'makeTypeRegistry'],
        TypeResolver::class          => [self::class, 'makeTypeResolver'],
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

    private function makeStandardServer(ServerConfig $config): StandardServer
    {
        return new StandardServer($config);
    }

    private function makeServerConfig(Schema $schema): ServerConfig
    {
        $config = (new ServerConfig())
            ->setSchema($schema);

        $config->setDebugFlag();

        return $config;
    }

    private function makeSchema(SchemaConfig $config): Schema
    {
        return new Schema($config);
    }

    private function makeSchemaConfig(TypeRegistryInterface $typeRegistry): SchemaConfig
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

    private function makeTypeRegistry(): TypeRegistry
    {
        return new TypeRegistry();
    }

    private function makeTypeResolver(
        ContainerInterface $container,
        GraphQLConfig $config,
    ): TypeResolver {
        $typeResolver = new TypeResolver();

        foreach ($config->getTypeResolverMiddlewares() as $name) {
            $priority = (new ReflectionClass($name))->getConstant('PRIORITY') ?: 0;

            $middleware = $container->get($name);
            $typeResolver->pipe($middleware, (int) $priority);
        }

        return $typeResolver;
    }
}
