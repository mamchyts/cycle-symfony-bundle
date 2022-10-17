<?php

declare(strict_types=1);

namespace Cycle\SymfonyBundle\Command;

use Cycle\Database\DatabaseProviderInterface;
use Cycle\Migrations\Migrator;
use Cycle\ORM\ORMInterface;
use Cycle\SymfonyBundle\Migration\FileRepository;
use Cycle\SymfonyBundle\Service\MigrationService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'cycle:migration:migrate',
    description: 'Run migration process between your current database and mapping information',
)]
class MigrationMigrateCommand extends Command
{
    public function __construct(
        private DatabaseProviderInterface $dbal,
        private FileRepository $fileRepository,
        private MigrationService $migrationService,
        private ORMInterface $orm
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // HACK for getting correct Registry object after scheme compilation
        unset($this->orm);

        $migrationConfig = $this->migrationService->getMigrationConfig();

        $migrator = new Migrator(
            $migrationConfig,
            $this->dbal,
            $this->fileRepository
        );
        $migrator->configure();

        $io = new SymfonyStyle($input, $output);

        try {
            while (($migration = $migrator->run()) !== null) {
                $io->success('Migrate ' . $migration->getState()->getName());
            }
        } catch (\Throwable $th) {
            $io->error($th->getMessage());

            return Command::FAILURE;
        }

        $io->success('Success');

        return Command::SUCCESS;
    }
}
