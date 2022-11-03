<?php

declare(strict_types=1);

namespace Cycle\SymfonyBundle\Factory;

use Cycle\Annotated\{Embeddings, Entities, MergeColumns, MergeIndexes, TableInheritance};
use Cycle\Database\DatabaseProviderInterface;
use Cycle\ORM\Collection\DoctrineCollectionFactory;
use Cycle\ORM\{Factory, ORM, ORMInterface, Schema};
use Cycle\Schema\Generator\{GenerateModifiers, GenerateRelations, GenerateTypecast, RenderModifiers, RenderRelations, RenderTables, ResetTables, ValidateEntities};
use Cycle\Schema\{Compiler, Registry};
use Cycle\SymfonyBundle\Service\ConfigService;

class OrmFactory
{
    public function __construct(
        private ConfigService $configService,
        private DatabaseProviderInterface $dbal,
        private Registry $registry,
    ) {
    }

    public function createOrm(): ORMInterface
    {
        // load classes Entities/Repositories
        $finder = (new \Symfony\Component\Finder\Finder())->files()->in($this->configService->getConfigs()['orm']['schema']['directory']);
        $classLocator = new \Spiral\Tokenizer\ClassLocator($finder);

        // generate DB schema
        $schema = (new Compiler())->compile(
            $this->registry,
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
            ],
        );

        return new ORM(
            new Factory(
                dbal: $this->dbal,
                defaultCollectionFactory: new DoctrineCollectionFactory(),
            ),
            new Schema($schema),
        );
    }
}
