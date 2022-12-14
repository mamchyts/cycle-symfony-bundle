<?php

declare(strict_types=1);

namespace Cycle\SymfonyBundle;

use Cycle\SymfonyBundle\DependencyInjection\Compiler\{FixturePass, RepositoryPass};
use Cycle\SymfonyBundle\DependencyInjection\Security\{EntityUserProvider, EntityUserProviderFactory};
use Cycle\SymfonyBundle\Fixture\AbstractFixture;
use Cycle\SymfonyBundle\Repository\CycleServiceRepository;
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
                            ->scalarNode('database')->defaultNull()->end()
                            ->scalarNode('dsn')->defaultNull()->end()
                            ->scalarNode('host')->defaultNull()->end()
                            ->scalarNode('socket')->defaultNull()->end()
                            ->integerNode('port')->defaultNull()->end()
                            ->scalarNode('user')->defaultValue('root')->end()
                            ->scalarNode('password')->defaultValue('root')->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('orm')
                    ->children()
                        ->arrayNode('schema')
                            ->children()
                                ->scalarNode('directory')->defaultValue('%kernel.project_dir%/src/Entity')->isRequired()->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('migration')
                    ->children()
                        ->scalarNode('directory')->defaultValue('%kernel.project_dir%/migrations')->isRequired()->end()
                        ->scalarNode('table')->defaultValue('migrations')->isRequired()->end()
                        ->scalarNode('safe')->defaultTrue()->isRequired()->end()
                    ->end()
                ->end()
            ->end();
    }

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $this->addCompilerPass($container);

        // try to add `entity` provider into ./config/packages/security.yaml
        if ($container->hasExtension('security')) {
            $security = $container->getExtension('security');
            if ($security instanceof SecurityExtension) {
                $security->addUserProviderFactory(new EntityUserProviderFactory('entity', EntityUserProvider::class));
            }
        }
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('../config/services.yaml');

        $container->parameters()->set(self::CYCLE_PARAMETER_CONFIG, $config);
    }

    private function addCompilerPass(ContainerBuilder $container): void
    {
        // autoload user repositories
        $container
            ->registerForAutoconfiguration(CycleServiceRepository::class)
            ->addTag(RepositoryPass::REPOSITORY_TAG);
        $container->addCompilerPass(new RepositoryPass());

        // autoload user fixtures
        $container
            ->registerForAutoconfiguration(AbstractFixture::class)
            ->addTag(FixturePass::FIXTURE_TAG);
        $container->addCompilerPass(new FixturePass());
    }
}
