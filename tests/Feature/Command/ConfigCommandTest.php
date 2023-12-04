<?php

declare(strict_types=1);

namespace Andi\Tests\GraphQL\Spiral\Feature\Command;

use Andi\GraphQL\Spiral\Bootloader\GraphQLBootloader;
use Andi\GraphQL\Spiral\Command\ConfigCommand;
use Andi\GraphQL\Spiral\Config\GraphQLConfig;
use Andi\GraphQL\Spiral\Listener\AbstractAdditionalFieldListener;
use Andi\GraphQL\Spiral\Listener\AdditionalFieldListener;
use Andi\GraphQL\Spiral\Listener\AttributedMutationFieldListener;
use Andi\GraphQL\Spiral\Listener\AttributedQueryFieldListener;
use Andi\GraphQL\Spiral\Listener\AttributedTypeLoaderListener;
use Andi\GraphQL\Spiral\Listener\MutationFieldListener;
use Andi\GraphQL\Spiral\Listener\QueryFieldListener;
use Andi\GraphQL\Spiral\Listener\TypeLoaderListener;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Spiral\Testing\TestCase;

#[CoversClass(ConfigCommand::class)]
#[UsesClass(GraphQLBootloader::class)]
#[UsesClass(GraphQLConfig::class)]
#[UsesClass(AbstractAdditionalFieldListener::class)]
#[UsesClass(AdditionalFieldListener::class)]
#[UsesClass(AttributedMutationFieldListener::class)]
#[UsesClass(AttributedQueryFieldListener::class)]
#[UsesClass(AttributedTypeLoaderListener::class)]
#[UsesClass(MutationFieldListener::class)]
#[UsesClass(QueryFieldListener::class)]
#[UsesClass(TypeLoaderListener::class)]
final class ConfigCommandTest extends TestCase
{
    public function rootDirectory(): string
    {
        return dirname(__DIR__, 2) . '/dirs';
    }

    public function defineBootloaders(): array
    {
        return [
            GraphQLBootloader::class,
        ];
    }

    public function testCommandIsRegistered(): void
    {
        $this->assertCommandRegistered('graphql:config');
        $output = $this->runCommand('graphql:config');

        $this->assertStringContainsString('Create default configuration into', $output);
    }
}
