<?php

declare(strict_types=1);

namespace Andi\GraphQL\Spiral\Command;

use Spiral\Boot\DirectoriesInterface;
use Spiral\Console\Attribute\AsCommand;
use Spiral\Console\Command;
use Spiral\Files\FilesInterface;

#[AsCommand(
    name: 'graphql:config',
    description: 'Create GraphQL config with default values',
    help: 'Dump default values into config/graphql.php file',
)]
final class ConfigCommand extends Command
{
    public function __construct(
        private readonly FilesInterface $files,
        private readonly DirectoriesInterface $dirs,
    ) {
        parent::__construct();
    }

    public function perform(): int
    {
        $this->info(sprintf('Create default configuration into: %sgraphql.php', $this->dirs->get('config')));

        $source = __DIR__ . '/../../config/graphql.php.sample';
        $destination = $this->dirs->get('config') . 'graphql.php';

        $this->files->copy($source, $destination);

        return self::SUCCESS;
    }
}
