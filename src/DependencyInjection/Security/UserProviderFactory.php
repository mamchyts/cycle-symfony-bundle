<?php

declare(strict_types=1);

namespace Cycle\SymfonyBundle\DependencyInjection\Security;

use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\UserProvider\UserProviderFactoryInterface;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\DependencyInjection\{ChildDefinition, ContainerBuilder};

class UserProviderFactory implements UserProviderFactoryInterface
{
    private string $key;
    private string $providerId;

    public function __construct(string $key, string $providerId)
    {
        $this->key = $key;
        $this->providerId = $providerId;
    }

    public function create(ContainerBuilder $container, string $id, array $config): void
    {
        $container
            ->setDefinition($id, new ChildDefinition($this->providerId))
            ->addArgument($config['class'])
            ->addArgument($config['property']);
    }

    public function getKey()
    {
        return $this->key;
    }

    public function addConfiguration(NodeDefinition $node): void
    {
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
