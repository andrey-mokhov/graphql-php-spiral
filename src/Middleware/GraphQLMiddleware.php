<?php

declare(strict_types=1);

namespace Andi\GraphQL\Spiral\Middleware;

use Andi\GraphQL\Spiral\Config\GraphQLConfig;
use GraphQL\Server\StandardServer;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Spiral\Core\Container\SingletonInterface;
use Spiral\Core\ScopeInterface;

final class GraphQLMiddleware implements MiddlewareInterface, SingletonInterface
{
    /** @todo its DTO */
    private readonly GreaphQLScope $scope;

    public function __construct(
        GraphQLConfig $config,
        private readonly ResponseFactoryInterface $responseFactory,
        private readonly StreamFactoryInterface $streamFactory,
        private readonly ScopeInterface $container,
        string $scope = 'default',
    ) {
        $this->scope = $config->getScope($scope);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($request->getUri()->getPath() === $this->scope->getUrl()) {
            $response = $this->responseFactory->createResponse();
            $stream = $this->streamFactory->createStream();

            $this->container->runScope([GreaphQLScope::class => $this->scope], static function (ContainerInterface $container) {
                return $container->get(StandardServer::class)->processPsrRequest();
            });

            $result = $this->server->processPsrRequest($request, $response, $stream);
            assert($result instanceof ResponseInterface);
            return $result;
        }

        return $handler->handle($request);
    }
}
