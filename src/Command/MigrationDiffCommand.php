<?php

declare(strict_types=1);

namespace Cycle\SymfonyBundle\Command;

use Cycle\Database\DatabaseProviderInterface;
use Cycle\Migrations\{FileRepository, Migrator};
use Cycle\ORM\ORMInterface;
use Cycle\Schema\Generator\Migrations\GenerateMigrations;
use Cycle\Schema\Registry;
use Cycle\SymfonyBundle\Service\{ConfigService, MigrationService, MigrationTemplateService};
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'cycle:migration:diff',
    description: 'Generate a diff migration between your current database and mapping information',
)]
class MigrationDiffCommand extends Command
{
    private array $parameters = [];

    public function __construct(
        private ConfigService $configService,
        private ORMInterface $orm,
        private DatabaseProviderInterface $dbal,
        private MigrationService $migrationService,
        private MigrationTemplateService $migrationTemplateService
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $cycleConfig = $this->configService->getConfigs();
        $migrationConfig = $this->migrationService->getMigrationConfig();

        $migrator = new Migrator(
            $migrationConfig,
            $this->dbal,
            new FileRepository($migrationConfig)
        );
        $migrator->configure();

        // load classes Entities/Repositories
        $finder = (new \Symfony\Component\Finder\Finder())->files()->in($cycleConfig['orm']['schema']['directory']);
        $classLocator = new \Spiral\Tokenizer\ClassLocator($finder);

        // $registry = new Registry($this->dbal);
        // $registry->register(....);

        $generator = new GenerateMigrations(
            $migrator->getRepository(),
            $migrator->getConfig()
        );

        $io->success('Success');

        return Command::SUCCESS;
    }
}
