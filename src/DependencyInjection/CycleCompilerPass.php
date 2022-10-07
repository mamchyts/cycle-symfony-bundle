<?php

declare(strict_types=1);

namespace Cycle\SymfonyBundle\DependencyInjection;

use Cycle\ORM;
use Cycle\ORM\Mapper\Mapper;
use Cycle\Schema;
use Cycle\Annotated;
use Cycle\Database\Config\ConnectionConfig;
use Cycle\Database\Config\DatabaseConfig;
use Cycle\Database\Config\DriverConfig;
use Cycle\Database\DatabaseManager;
use Cycle\ORM\ORMInterface;
use Cycle\SymfonyBundle\ConnectionFactory;
use Cycle\SymfonyBundle\SymfonyBundle;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class CycleCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $config = $container->getParameter(SymfonyBundle::CYCLE_PARAMETER_CONFIG);

        // detect default connection name
        $defaultConnection = null;
        foreach ($config['databases'] as $item) {
            if ($item['default'] === true) {
                $defaultConnection = $item['connection'];
                break;
            }
        }

        $databases = [];
        $connections = [];
        foreach ($config['connections'] as $key => $connection) {
            $databases[$key] = ['connection' => $key];

            $definition = (new Definition(ConnectionConfig::class))
                ->setFactory([ConnectionFactory::class, 'createConnectionConfig'])
                ->addArgument($connection);
            $serviceKeyConnectionConfig = 'cycle.orm.connection_config.' . $key;
            $container->setDefinition($serviceKeyConnectionConfig, $definition);

            $definition = (new Definition(DriverConfig::class))
                    ->setFactory([ConnectionFactory::class, 'createDriverConfig'])
                    ->addArgument(new Reference($serviceKeyConnectionConfig));
            $serviceKeyDriverConfig = 'cycle.orm.driver_config.' . $key;
            $container->setDefinition($serviceKeyDriverConfig, $definition);

            $connections[$key] = new Reference($serviceKeyDriverConfig);
        }

        $definition = new Definition(DatabaseConfig::class, [[
            'default' => $defaultConnection,
            'databases' => $databases,
            'connections' => $connections,
        ]]);
        $container->setDefinition($definition->getClass(), $definition);

        $definition = new Definition(DatabaseManager::class, [new Reference(DatabaseConfig::class)]);
        $container->setDefinition($definition->getClass(), $definition);

        // load classes
        $finder = (new \Symfony\Component\Finder\Finder())->files()->in($config['orm']['schema']['dir']);
        $classLocator = new \Spiral\Tokenizer\ClassLocator($finder);
        $classLocator->getClasses();



/*
        $databases = [];
        $connections = [];
        foreach ($config['connections'] as $key => $connection) {
            $databases[$key] = ['connection' => $key];
            $connections[$key] = ConnectionFactory::createDriverConfig(
                ConnectionFactory::createConnectionConfig($connection)
            );
        }

        $dbm = new DatabaseManager(
            new DatabaseConfig([
                'default' => $defaultConnection,
                'databases' => $databases,
                'connections' => $connections
            ])
        );

        // load classes
        $finder = (new \Symfony\Component\Finder\Finder())->files()->in($config['orm']['schema']['dir']);
        $classLocator = new \Spiral\Tokenizer\ClassLocator($finder);
        $classLocator->getClasses();

        $schema = (new Schema\Compiler())->compile(new Schema\Registry($dbm), [
            new Schema\Generator\ResetTables(),             // re-declared table schemas (remove columns)
            new Annotated\Embeddings($classLocator),        // register embeddable entities
            new Annotated\Entities($classLocator),          // register annotated entities
            new Annotated\TableInheritance(),               // register STI/JTI
            new Annotated\MergeColumns(),                   // add @Table column declarations
            new Schema\Generator\GenerateRelations(),       // generate entity relations
            new Schema\Generator\GenerateModifiers(),       // generate changes from schema modifiers
            new Schema\Generator\ValidateEntities(),        // make sure all entity schemas are correct
            new Schema\Generator\RenderTables(),            // declare table schemas
            new Schema\Generator\RenderRelations(),         // declare relation keys and indexes
            new Schema\Generator\RenderModifiers(),         // render all schema modifiers
            new Annotated\MergeIndexes(),                   // add @Table column declarations
            new Schema\Generator\GenerateTypecast(),        // typecast non string columns
        ]);

        $orm = new ORM\ORM(new ORM\Factory($dbm), new \Cycle\ORM\Schema($schema));

        // $container->set(ORMInterface::class, $orm);
*/

    }
}
