<?php

declare(strict_types=1);

namespace Andi\GraphQL\Spiral\Common;

use GraphQL\Language\AST\DocumentNode;
use GraphQL\Server\OperationParams;
use Spiral\Core\InvokerInterface;
use Spiral\Core\ScopeInterface;

final class ValueResolver
{
    private readonly \Closure $factoryFn;

    public function __construct(
        private readonly ScopeInterface $scope,
        private readonly InvokerInterface $invoker,
        callable $factory,
    ) {
        $this->factoryFn = $factory instanceof \Closure
            ? $factory
            : $factory(...);
    }

    public function __invoke(OperationParams $params, DocumentNode $doc, string $operationType): mixed
    {
        return $this->scope->runScope(
            [OperationParams::class => $params, DocumentNode::class => $doc],
            fn () => $this->invoker->invoke($this->factoryFn, ['operationType' => $operationType]),
        );
    }
}
