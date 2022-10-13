<?php

declare(strict_types=1);

namespace Cycle\SymfonyBundle\Command;

use Cycle\SymfonyBundle\Service\{MigrationService, MigrationTemplateService};
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'cycle:migration:generate',
    description: 'Generate a blank migration class',
)]
class MigrationGenerateCommand extends Command
{
    public function __construct(
        private MigrationService $migrationService,
        private MigrationTemplateService $migrationTemplateService
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $migrationClassName = $this->migrationTemplateService->generateClassName();
        $migrationFileContent = $this->migrationTemplateService->getEmptyMigration($migrationClassName);

        $migrationDir = $this->migrationService->getMigrationConfig()['directory'];
        $filePath = $this->migrationTemplateService->createMigrationFile($migrationDir, $migrationClassName, $migrationFileContent);

        $io = new SymfonyStyle($input, $output);
        $io->text('Generated new migration class: `' . $filePath . '`');
        $io->success('Success');

        return Command::SUCCESS;
    }
}
