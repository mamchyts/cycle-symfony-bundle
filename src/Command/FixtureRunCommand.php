<?php

declare(strict_types=1);

namespace Cycle\SymfonyBundle\Command;

use Cycle\ORM\EntityManagerInterface;
use Cycle\SymfonyBundle\Fixture\FixtureService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'cycle:fixture:run',
    description: 'Run all fixtures',
)]
class FixtureRunCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $em,
        private FixtureService $fixtureService,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        foreach ($this->fixtureService->getFixtures() as $fixture) {
            try {
                $fixture->load($this->em);

                $io->success('Fixture `' . $fixture::class . '` - done');
            } catch (\Throwable $th) {
                $io->error($th->getMessage());
                $io->writeln($th->getTraceAsString(), OutputInterface::VERBOSITY_VERY_VERBOSE);

                return Command::FAILURE;
            }
        }

        $io->success('Success');

        return Command::SUCCESS;
    }
}
