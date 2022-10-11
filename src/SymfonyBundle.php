<?php

declare(strict_types=1);

namespace Cycle\SymfonyBundle;

use Cycle\SymfonyBundle\DependencyInjection\Security\UserProviderFactory;
use Symfony\Bundle\SecurityBundle\DependencyInjection\SecurityExtension;
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
        /** @phpstan-ignore-next-line */
        $definition->rootNode()
            ->children()
                ->arrayNode('databases')
                    ->addDefaultChildrenIfNoneSet()
                    ->arrayPrototype()
                        ->children()
                            ->booleanNode('default')->defaultTrue()->end()
                            ->scalarNode('connection')->defaultValue('primary')->end()
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
                    ->addDefaultChildrenIfNoneSet()
                    ->arrayPrototype()
                        ->children()
                            ->enumNode('type')->values(['mysql', 'pgsql', 'sqlite', 'sqlsrv'])->end()
                            ->scalarNode('charset')->defaultNull()->end()
                            ->scalarNode('database')->defaultValue('db_name')->end()
                            ->scalarNode('dsn')->defaultValue('mysql:host=127.0.0.1;port=3306;dbname=db_name')->end()
                            ->scalarNode('host')->defaultValue('127.0.0.1')->end()
                            ->scalarNode('socket')->defaultValue('/tmp/mysql.sock')->end()
                            ->integerNode('port')->defaultValue(3306)->end()
                            ->scalarNode('user')->defaultValue('root')->end()
                            ->scalarNode('password')->defaultValue('root')->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('orm')
                    ->children()
                        ->arrayNode('schema')
                            ->children()
                                ->scalarNode('dir')->defaultValue('./src/Entity')->isRequired()->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        // try to add `entity` provider into ./config/packages/security.yaml
        if ($container->hasExtension('security')) {
            $security = $container->getExtension('security');

            if ($security instanceof SecurityExtension) {
                $security->addUserProviderFactory(new UserProviderFactory('entity', 'cycle.orm.security.user.provider'));
            }
        }
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('../config/services.yaml');

        $container->parameters()->set(self::CYCLE_PARAMETER_CONFIG, $config);
    }
}
