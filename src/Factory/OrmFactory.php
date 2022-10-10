<?php

declare(strict_types=1);

namespace Cycle\SymfonyBundle\Factory;

use Cycle\Annotated\{Embeddings, Entities, MergeColumns, MergeIndexes, TableInheritance};
use Cycle\Database\DatabaseProviderInterface;
use Cycle\ORM\{Factory, ORM, ORMInterface, Schema};
use Cycle\Schema\Generator\{GenerateModifiers, GenerateRelations, GenerateTypecast, RenderModifiers, RenderRelations, RenderTables, ResetTables, ValidateEntities};
use Cycle\Schema\{Compiler, Registry};
use Cycle\SymfonyBundle\SymfonyBundle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class OrmFactory
{
    private array $parameters = [];

    public function __construct(
        private DatabaseProviderInterface $databaseManager,
        ParameterBagInterface $parameterBag
    ) {
        /** @var array */
        $parameters = $parameterBag->get(SymfonyBundle::CYCLE_PARAMETER_CONFIG);

        $this->parameters = $parameters;
    }

    public function createOrm(): ORMInterface
    {
        // load classes Entities/Repositories
        $finder = (new \Symfony\Component\Finder\Finder())->files()->in($this->parameters['orm']['schema']['dir']);
        $classLocator = new \Spiral\Tokenizer\ClassLocator($finder);
        $classLocator->getClasses();

        // generate DB schema
        $schema = (new Compiler())->compile(
            new Registry($this->databaseManager),
            [
                new ResetTables(),             // re-declared table schemas (remove columns)
                new Embeddings($classLocator), // register embeddable entities
                new Entities($classLocator),   // register annotated entities
                new TableInheritance(),        // register STI/JTI
                new MergeColumns(),            // add @Table column declarations
                new GenerateRelations(),       // generate entity relations
                new GenerateModifiers(),       // generate changes from schema modifiers
                new ValidateEntities(),        // make sure all entity schemas are correct
                new RenderTables(),            // declare table schemas
                new RenderRelations(),         // declare relation keys and indexes
                new RenderModifiers(),         // render all schema modifiers
                new MergeIndexes(),            // add @Table column declarations
                new GenerateTypecast(),        // typecast non string columns
            ]
        );

        return new ORM(
            new Factory($this->databaseManager),
            new Schema($schema)
        );
    }
}
