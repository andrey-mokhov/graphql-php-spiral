<?php

declare(strict_types=1);

namespace Andi\GraphQL\Spiral\Config;

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
            WebonyxGraphQLTypeMiddleware::class,
            GraphQLTypeMiddleware::class,
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
     * @return array<int, class-string>
     */
    public function getTypeResolverMiddlewares(): array
    {
        return $this->config['typeResolverMiddlewares'];
    }
}
