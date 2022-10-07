<?php

declare(strict_types=1);

namespace Cycle\SymfonyBundle;

use Cycle\SymfonyBundle\DependencyInjection\CycleCompilerPass;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class SymfonyBundle extends AbstractBundle
{
    public const CYCLE_PARAMETER_CONFIG = 'cycle.config';

    protected string $extensionAlias = 'cycle';

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
            ->children()
                ->arrayNode('databases')
                    ->arrayPrototype()
                        ->children()
                            ->booleanNode('default')->defaultFalse()->end()
                            ->scalarNode('connection')->end()
                        ->end()
                    ->end()
                    ->validate()
                        ->ifTrue(static function (array $databases) {
                            foreach ($databases as $db) {
                                if ($db['default'] === true) {
                                    return false;
                                }
                            }

                            return true;
                        })
                        ->thenInvalid('One database connection must by marked as `default`')
                    ->end()
                ->end()
                ->arrayNode('connections')
                    ->arrayPrototype()
                        ->children()
                            ->enumNode('type')->values(['mysql', 'pgsql', 'sqlite', 'sqlsrv'])->end()
                            ->scalarNode('charset')->defaultNull()->end()
                            ->scalarNode('database')->defaultNull()->end()
                            ->scalarNode('dsn')->defaultNull()->end()
                            ->scalarNode('host')->defaultNull()->end()
                            ->scalarNode('socket')->defaultNull()->end()
                            ->integerNode('port')->defaultNull()->end()
                            ->scalarNode('user')->defaultValue('')->end()
                            ->scalarNode('password')->defaultValue('')->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('orm')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('dir')->isRequired()->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new CycleCompilerPass());
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->parameters()->set(self::CYCLE_PARAMETER_CONFIG, $config);
    }
}
