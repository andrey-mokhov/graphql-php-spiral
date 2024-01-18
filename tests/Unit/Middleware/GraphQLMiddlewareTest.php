<?php

declare(strict_types=1);

namespace Andi\Tests\GraphQL\Spiral\Unit\Middleware;

use Andi\GraphQL\Spiral\Config\GraphQLConfig;
use Andi\GraphQL\Spiral\Middleware\GraphQLMiddleware;
use GraphQL\Server\StandardServer;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

#[CoversClass(GraphQLMiddleware::class)]
#[UsesClass(GraphQLConfig::class)]
final class GraphQLMiddlewareTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private GraphQLMiddleware $middleware;

    private GraphQLConfig $config;

    private StandardServer $server;

    private ResponseFactoryInterface $responseFactory;

    private StreamFactoryInterface $streamFactory;

    protected function setUp(): void
    {
        $this->middleware = new GraphQLMiddleware(
            $this->config = new GraphQLConfig(),
            $this->server = \Mockery::mock(StandardServer::class),
            $this->responseFactory = \Mockery::mock(ResponseFactoryInterface::class),
            $this->streamFactory = \Mockery::mock(StreamFactoryInterface::class),
        );
    }

    public function testInstanceOf(): void
    {
        self::assertInstanceOf(MiddlewareInterface::class, $this->middleware);
    }

    public function testCallNextHandler(): void
    {
        $handler = \Mockery::mock(RequestHandlerInterface::class);
        $handler->shouldReceive('handle')->once()->andReturn($response = \Mockery::mock(ResponseInterface::class));

        $serverRequest = new ServerRequest('POST', '/url');

        $result = $this->middleware->process($serverRequest, $handler);

        self::assertSame($response, $result);
    }

    public function testProcess(): void
    {
        $serverRequest = new ServerRequest('POST', $this->config->getUrl());
        $response = \Mockery::mock(ResponseInterface::class);

        $this->responseFactory->shouldReceive('createResponse')->once()->andReturn($response);
        $this->streamFactory->shouldReceive('createStream')->once()->andReturn(\Mockery::mock(StreamInterface::class));

        $this->server->shouldReceive('processPsrRequest')->once()->andReturn($response);

        $handler = \Mockery::mock(RequestHandlerInterface::class);
        $handler->shouldReceive('handle')->never();

        $result = $this->middleware->process($serverRequest, $handler);

        self::assertSame($response, $result);
    }
}
