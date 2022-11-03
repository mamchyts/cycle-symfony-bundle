<?php

declare(strict_types=1);

namespace Cycle\SymfonyBundle\Command;

use Cycle\Database\{DatabaseManager, DatabaseProviderInterface, Table};
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'cycle:database:drop-tables',
    description: 'Drop all tables in database(s)',
)]
class DatabaseDropTablesCommand extends Command
{
    public function __construct(
        private DatabaseProviderInterface $dbal,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // @todo DatabaseProviderInterface must declare `getDatabases()` method
        if (!$this->dbal instanceof DatabaseManager) {
            $io->error('Invalid $dbal instance: ' . $this->dbal::class);

            return Command::FAILURE;
        }

        foreach ($this->dbal->getDatabases() as $database) {
            // at the first - generate hash of all tables
            $tables = [];
            foreach ($database->getTables() as $table) {
                $tables[$table->getName()] = $table;
            }

            // at the second - delete tables
            $this->deleteTables($tables);
        }

        $io->success('Success');

        return Command::SUCCESS;
    }

    /** @param Table[] $tables */
    protected function deleteTables(array $tables): void
    {
        // at the first remove foreign keys
        foreach ($tables as $table) {
            $schema = $table->getSchema();

            foreach ($schema->getForeignKeys() as $foreignKey) {
                $schema->dropForeignKey($foreignKey->getColumns());
            }

            $schema->save();
        }

        // at the second remove tables
        foreach ($tables as $table) {
            $schema = $table->getSchema();

            $schema->declareDropped();
            $schema->save();
        }
    }
}
