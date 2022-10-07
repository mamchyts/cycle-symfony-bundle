<?php

declare(strict_types=1);

namespace Cycle\SymfonyBundle\Repository;

use Cycle\ORM\ORMInterface;

class CycleServiceRepository extends \Cycle\ORM\Select\Repository
{
    public function __construct(ORMInterface $orm, string $entityName)
    {
        echo '<pre>'; print_r([$orm, $entityName]); echo '</pre>';die();
    }
}
