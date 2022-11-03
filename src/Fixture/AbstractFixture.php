<?php

declare(strict_types=1);

namespace Cycle\SymfonyBundle\Fixture;

use Cycle\ORM\EntityManagerInterface;
use Cycle\SymfonyBundle\Exception\AbstractException;

abstract class AbstractFixture
{
    private array $storage = [];

    abstract public function load(EntityManagerInterface $em): void;

    protected function addReference(string|int $key, mixed $value): void
    {
        $this->storage[$key] = $value;
    }

    /** @throws AbstractException */
    protected function getReference(string|int $key): mixed
    {
        return $this->storage[$key] ?? throw new AbstractException('Reference for key `' . $key . '` not exists');
    }
}
