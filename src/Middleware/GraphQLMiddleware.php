<?php

declare(strict_types=1);

namespace Andi\GraphQL\Spiral\Middleware;

use Andi\GraphQL\Spiral\Config\GraphQLConfig;
use GraphQL\Server\ServerConfig;
use GraphQL\Server\StandardServer;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Spiral\Core\Container\SingletonInterface;

final class GraphQLMiddleware implements MiddlewareInterface, SingletonInterface
{
    public function __construct(
        private readonly GraphQLConfig $config,
        private readonly StandardServer $server,
        private readonly ResponseFactoryInterface $responseFactory,
        private readonly StreamFactoryInterface $streamFactory,
        private readonly ContainerInterface $container,
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($request->getUri()->getPath() === $this->config->getUrl()) {
            $response = $this->responseFactory->createResponse();
            $stream = $this->streamFactory->createStream();

            if ($contextClass = $this->config->getContextClass()) {
                $serverConfig = $this->container->get(ServerConfig::class);
                $serverConfig->setContext($this->container->get($contextClass));
            }

            return $this->server->processPsrRequest($request, $response, $stream);
        }

        return $handler->handle($request);
    }
}
