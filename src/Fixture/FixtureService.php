<?php

declare(strict_types=1);

namespace Cycle\SymfonyBundle\Fixture;

class FixtureService
{
    public function __construct(
        private array $fixtures,
    ) {
    }

    /** @return AbstractFixture[] */
    public function getFixtures(): array
    {
        return $this->fixtures;
    }
}
