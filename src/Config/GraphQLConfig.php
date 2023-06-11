<?php

declare(strict_types=1);

namespace Andi\GraphQL\Spiral\Config;

use Andi\GraphQL\ArgumentResolver\Middleware\ArgumentConfigurationMiddleware;
use Andi\GraphQL\ArgumentResolver\Middleware\ArgumentMiddleware;
use Andi\GraphQL\InputObjectFieldResolver\Middleware\InputObjectFieldMiddleware;
use Andi\GraphQL\InputObjectFieldResolver\Middleware\WebonyxInputObjectFieldMiddleware;
use Andi\GraphQL\ObjectFieldResolver\Middleware\ObjectFieldMiddleware;
use Andi\GraphQL\ObjectFieldResolver\Middleware\WebonyxObjectFieldMiddleware;
use Andi\GraphQL\TypeResolver\Middleware\GraphQLTypeMiddleware;
use Andi\GraphQL\TypeResolver\Middleware\WebonyxGraphQLTypeMiddleware;
use Spiral\Core\InjectableConfig;

final class GraphQLConfig extends InjectableConfig
{
    public const CONFIG = 'graphql';

    public const DEFAULT_QUERY_TYPE = 'Query';
    public const DEFAULT_MUTATION_TYPE = 'Mutation';

    protected array $config = [
        'url'          => '/api/graphql',
        'queryType'    => self::DEFAULT_QUERY_TYPE,
        'mutationType' => null,
        'contextClass' => null,

        'typeResolverMiddlewares' => [
            WebonyxGraphQLTypeMiddleware::class => WebonyxGraphQLTypeMiddleware::PRIORITY,
            GraphQLTypeMiddleware::class        => GraphQLTypeMiddleware::PRIORITY,
        ],

        'objectFieldResolverMiddlewares' => [
            ObjectFieldMiddleware::class        => ObjectFieldMiddleware::PRIORITY,
            WebonyxObjectFieldMiddleware::class => WebonyxObjectFieldMiddleware::PRIORITY,
        ],

        'inputObjectFieldResolverMiddlewares' => [
            InputObjectFieldMiddleware::class        => InputObjectFieldMiddleware::PRIORITY,
            WebonyxInputObjectFieldMiddleware::class => WebonyxInputObjectFieldMiddleware::PRIORITY,
        ],

        'argumentResolverMiddlewares' => [
            ArgumentMiddleware::class              => ArgumentMiddleware::PRIORITY,
            ArgumentConfigurationMiddleware::class => ArgumentConfigurationMiddleware::PRIORITY,
        ],
    ];

    public function getUrl(): string
    {
        return $this->config['url'];
    }

    public function getQueryType(): string
    {
        return $this->config['queryType'];
    }

    public function getMutationType(): ?string
    {
        return $this->config['mutationType'];
    }

    /**
     * @return class-string|null
     */
    public function getContextClass(): ?string
    {
        return $this->config['contextClass'] ?? null;
    }

    /**
     * @return array<class-string,int>
     */
    public function getTypeResolverMiddlewares(): array
    {
        return $this->config['typeResolverMiddlewares'];
    }

    /**
     * @return array<class-string,int>
     */
    public function getObjectFieldResolverMiddlewares(): array
    {
        return $this->config['objectFieldResolverMiddlewares'];
    }

    /**
     * @return array<class-string,int>
     */
    public function getInputObjectFieldResolverMiddlewares(): array
    {
        return $this->config['inputObjectFieldResolverMiddlewares'];
    }

    /**
     * @return array<class-string,int>
     */
    public function getArgumentResolverMiddlewares(): array
    {
        return $this->config['argumentResolverMiddlewares'];
    }
}
