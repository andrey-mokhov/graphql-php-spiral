<?php

declare(strict_types=1);

namespace Andi\GraphQL\Spiral\Config;

use Andi\GraphQL\ArgumentResolver\ArgumentResolverInterface;
use Andi\GraphQL\ArgumentResolver\Middleware as Argument;
use Andi\GraphQL\InputObjectFieldResolver\InputObjectFieldResolverInterface;
use Andi\GraphQL\InputObjectFieldResolver\Middleware as Inputs;
use Andi\GraphQL\ObjectFieldResolver\Middleware as Objects;
use Andi\GraphQL\ObjectFieldResolver\ObjectFieldResolverInterface;
use Andi\GraphQL\TypeResolver\Middleware as Types;
use Andi\GraphQL\TypeResolver\TypeResolverInterface;
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
        'rootValue'    => null,
        'context'      => null,

        'typeResolverMiddlewares' => [
            Types\EnumTypeMiddleware::class              => Types\EnumTypeMiddleware::PRIORITY,
            Types\WebonyxGraphQLTypeMiddleware::class    => Types\WebonyxGraphQLTypeMiddleware::PRIORITY,
            Types\GraphQLTypeMiddleware::class           => Types\GraphQLTypeMiddleware::PRIORITY,
            Types\AttributedGraphQLTypeMiddleware::class => Types\AttributedGraphQLTypeMiddleware::PRIORITY,
        ],

        'objectFieldResolverMiddlewares' => [
            Objects\QueryFieldByReflectionMethodMiddleware::class      => Objects\QueryFieldByReflectionMethodMiddleware::PRIORITY,
            Objects\MutationFieldByReflectionMethodMiddleware::class   => Objects\MutationFieldByReflectionMethodMiddleware::PRIORITY,
            Objects\AdditionalFieldByReflectionMethodMiddleware::class => Objects\AdditionalFieldByReflectionMethodMiddleware::PRIORITY,
            Objects\InterfaceFieldByReflectionMethodMiddleware::class  => Objects\InterfaceFieldByReflectionMethodMiddleware::PRIORITY,
            Objects\ObjectFieldByReflectionMethodMiddleware::class     => Objects\ObjectFieldByReflectionMethodMiddleware::PRIORITY,
            Objects\ObjectFieldByReflectionPropertyMiddleware::class   => Objects\ObjectFieldByReflectionPropertyMiddleware::PRIORITY,
            Objects\ObjectFieldMiddleware::class                       => Objects\ObjectFieldMiddleware::PRIORITY,
            Objects\WebonyxObjectFieldMiddleware::class                => Objects\WebonyxObjectFieldMiddleware::PRIORITY,
        ],

        'inputObjectFieldResolverMiddlewares' => [
            Inputs\ReflectionPropertyMiddleware::class      => Inputs\ReflectionPropertyMiddleware::PRIORITY,
            Inputs\ReflectionMethodMiddleware::class        => Inputs\ReflectionMethodMiddleware::PRIORITY,
            Inputs\InputObjectFieldMiddleware::class        => Inputs\InputObjectFieldMiddleware::PRIORITY,
            Inputs\WebonyxInputObjectFieldMiddleware::class => Inputs\WebonyxInputObjectFieldMiddleware::PRIORITY,
        ],

        'argumentResolverMiddlewares' => [
            Argument\ReflectionParameterMiddleware::class   => Argument\ReflectionParameterMiddleware::PRIORITY,
            Argument\ArgumentMiddleware::class              => Argument\ArgumentMiddleware::PRIORITY,
            Argument\ArgumentConfigurationMiddleware::class => Argument\ArgumentConfigurationMiddleware::PRIORITY,
        ],

        'additionalTypes' => [
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

    public function getRootValue(): ?string
    {
        return $this->config['rootValue'];
    }

    /**
     * @return class-string|null
     */
    public function getContext(): ?string
    {
        return $this->config['context'] ?? null;
    }

    /**
     * @return array<class-string<TypeResolverInterface>,int>
     */
    public function getTypeResolverMiddlewares(): array
    {
        return $this->config['typeResolverMiddlewares'];
    }

    /**
     * @return array<class-string<ObjectFieldResolverInterface>,int>
     */
    public function getObjectFieldResolverMiddlewares(): array
    {
        return $this->config['objectFieldResolverMiddlewares'];
    }

    /**
     * @return array<class-string<InputObjectFieldResolverInterface>,int>
     */
    public function getInputObjectFieldResolverMiddlewares(): array
    {
        return $this->config['inputObjectFieldResolverMiddlewares'];
    }

    /**
     * @return array<class-string<ArgumentResolverInterface>,int>
     */
    public function getArgumentResolverMiddlewares(): array
    {
        return $this->config['argumentResolverMiddlewares'];
    }

    /**
     * @return array<class-string, string[]>
     */
    public function getAdditionalTypes(): array
    {
        return $this->config['additionalTypes'];
    }
}
