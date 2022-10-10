<?php

declare(strict_types=1);

namespace Cycle\SymfonyBundle\Repository;

use Cycle\ORM\Select\Repository;
use Cycle\ORM\{ORMInterface, Select};

/** @phpstan-ignore-next-line */
class CycleServiceRepository extends Repository
{
    public function __construct(
        private ORMInterface $orm,
        private string $entityName
    ) {
        parent::__construct($this->createSelect());
    }

    /** @phpstan-ignore-next-line */
    private function createSelect(): Select
    {
        $select = new Select($this->orm, $this->entityName);
        $select->scope($this->orm->getSource($this->entityName)->getScope());

        return $select;
    }
}
