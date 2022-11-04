<?php

declare(strict_types=1);

namespace Cycle\SymfonyBundle\Command;

use Cycle\Database\DatabaseProviderInterface;
use Cycle\Migrations\Migrator;
use Cycle\ORM\ORMInterface;
use Cycle\Schema\Generator\Migrations\GenerateMigrations;
use Cycle\Schema\Registry;
use Cycle\SymfonyBundle\Migration\FileRepository;
use Cycle\SymfonyBundle\Service\MigrationService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'cycle:migration:diff',
    description: 'Generate a diff migration between your current database and mapping information',
)]
class MigrationDiffCommand extends Command
{
    public function __construct(
        private DatabaseProviderInterface $dbal,
        private FileRepository $fileRepository,
        private MigrationService $migrationService,
        private ORMInterface $orm,
        private Registry $registry,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // @todo HACK for getting correct Registry object after scheme compilation
        // more into in OrmFactory::createOrm() - general point in $classLocator (we must load classes Entities/Repositories)
        unset($this->orm);

        $migrationConfig = $this->migrationService->getMigrationConfig();

        $migrator = new Migrator(
            $migrationConfig,
            $this->dbal,
            $this->fileRepository,
        );
        $migrator->configure();

        $migrationRepository = $migrator->getRepository();
        $generator = new GenerateMigrations($migrationRepository, $migrator->getConfig());

        $io = new SymfonyStyle($input, $output);

        try {
            $generator->run($this->registry);
        } catch (\Throwable $th) {
            $io->error($th->getMessage());

            return Command::FAILURE;
        }

        $io->success('Success');

        return Command::SUCCESS;
    }
}
