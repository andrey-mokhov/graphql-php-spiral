<?php

declare(strict_types=1);

use Spiral\Http\Middleware\JsonPayloadMiddleware;

return [
    'middleware' => [
        JsonPayloadMiddleware::class,
    ],
];
