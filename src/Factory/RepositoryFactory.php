<?php

declare(strict_types=1);

namespace Cycle\SymfonyBundle\Factory;

use Cycle\ORM\ORMInterface;
use Cycle\SymfonyBundle\Repository\CycleServiceRepository;

class RepositoryFactory
{
    public static function create(ORMInterface $orm, string $entityClassName): CycleServiceRepository
    {
        return $orm->getRepository($entityClassName);
    }
}
