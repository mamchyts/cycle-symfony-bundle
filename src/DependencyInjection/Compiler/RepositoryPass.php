<?php

declare(strict_types=1);

namespace Cycle\SymfonyBundle\DependencyInjection\Compiler;

use Cycle\ORM\ORMInterface;
use Cycle\SymfonyBundle\Factory\RepositoryFactory;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\{ContainerBuilder, Reference};

class RepositoryPass implements CompilerPassInterface
{
    public const REPOSITORY_TAG = 'cycle.orm.service_repository';

    public function process(ContainerBuilder $container): void
    {
        $services = $container
            ->findTaggedServiceIds(self::REPOSITORY_TAG);

        foreach (array_keys($services) as $repositoryClassName) {
            $container->register($repositoryClassName, $repositoryClassName)
                ->setFactory([new Reference(RepositoryFactory::class), 'create'])
                ->addArgument(new Reference(ORMInterface::class))
                ->addArgument(\call_user_func_array([$repositoryClassName, 'getEntityClass'], []));
        }
    }
}
