<?php

declare(strict_types=1);

namespace Cycle\SymfonyBundle\Command;

use Cycle\SymfonyBundle\Migration\FileService;
use Cycle\SymfonyBundle\Service\ConfigService;
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
        private ConfigService $configService,
        private FileService $fileService,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $className = $this->fileService->generateClassName();
        $fileContent = $this->fileService->getEmptyMigration($className, $this->configService->getDefaultConnection());

        $filePath = $this->fileService->createMigrationFile($className, $fileContent);

        $io = new SymfonyStyle($input, $output);
        $io->text('Generated new migration class: `' . $filePath . '`');
        $io->success('Success');

        return Command::SUCCESS;
    }
}
