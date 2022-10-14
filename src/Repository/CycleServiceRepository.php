<?php

declare(strict_types=1);

namespace Cycle\SymfonyBundle\Repository;

use Cycle\ORM\Select\Repository;

/** @phpstan-ignore-next-line */
abstract class CycleServiceRepository extends Repository
{
    abstract public static function getEntityClass(): string;
}
