<?php

declare(strict_types=1);

namespace Cycle\SymfonyBundle\DependencyInjection\Security;

use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\UserProvider\UserProviderFactoryInterface;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\DependencyInjection\{ChildDefinition, ContainerBuilder};

class EntityUserProviderFactory implements UserProviderFactoryInterface
{
    private string $key;
    private string $providerClassName;

    public function __construct(string $key, string $providerClassName)
    {
        $this->key = $key;
        $this->providerClassName = $providerClassName;
    }

    public function create(ContainerBuilder $container, string $id, array $config): void
    {
        $container
            ->setDefinition($id, new ChildDefinition($this->providerClassName))
            ->addArgument($config['class'])
            ->addArgument($config['property']);
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function addConfiguration(NodeDefinition $node): void
    {
        /** @phpstan-ignore-next-line */
        $node
            ->children()
                ->scalarNode('class')
                    ->isRequired()
                    ->info('The full entity class name of your user class.')
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('property')->defaultNull()->end()
            ->end();
    }
}
