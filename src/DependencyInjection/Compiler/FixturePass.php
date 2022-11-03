<?php

declare(strict_types=1);

namespace Cycle\SymfonyBundle\DependencyInjection\Compiler;

use Cycle\SymfonyBundle\Fixture\FixtureService;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\{ContainerBuilder, Reference};

class FixturePass implements CompilerPassInterface
{
    public const FIXTURE_TAG = 'cycle.orm.fixture';

    public function process(ContainerBuilder $container): void
    {
        $services = $container
            ->findTaggedServiceIds(self::FIXTURE_TAG);

        $fixtures = [];
        foreach (array_keys($services) as $fixtureClassName) {
            $fixtures[] = new Reference($fixtureClassName);
        }

        $container
            ->register(FixtureService::class, FixtureService::class)
            ->addArgument($fixtures);
    }
}
