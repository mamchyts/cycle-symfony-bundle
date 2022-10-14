<?php

declare(strict_types=1);

namespace Cycle\SymfonyBundle\Migration;

use Cycle\Migrations\Exception\RepositoryException;
use Cycle\Migrations\{RepositoryInterface, State};
use Spiral\Core\{Container, FactoryInterface};
use Spiral\Tokenizer\Reflection\ReflectionFile;

/**
 * Stores migrations as files.
 */
final class FileRepository implements RepositoryInterface
{
    private FactoryInterface $factory;

    public function __construct(private FileService $fileService)
    {
        $this->factory = new Container();
    }

    /**
     * {@inheritdoc}
     */
    public function getMigrations(): array
    {
        $timestamps = [];
        $chunks = [];
        $migrations = [];

        foreach ($this->getFiles() as $f) {
            if (!class_exists($f['class'], false)) {
                // Attempting to load migration class (we can not relay on autoloading here)
                require_once $f['filename'];
            }

            /** @var MigrationInterface $migration */
            $migration = $this->factory->make($f['class']);

            $timestamps[] = $f['created']->getTimestamp();
            $chunks[] = $f['chunk'];
            $migrations[] = $migration->withState(new State($f['name'], $f['created']));
        }

        array_multisort($timestamps, $chunks, \SORT_ASC | \SORT_NATURAL, $migrations);

        return $migrations;
    }

    /**
     * {@inheritdoc}
     */
    public function registerMigration(string $name, string $class, ?string $body = null): string
    {
        if (empty($body) && !class_exists($class)) {
            throw new RepositoryException("Unable to register migration '{$class}', representing class does not exists");
        }

        foreach ($this->getMigrations() as $migration) {
            if ($migration::class === $class) {
                throw new RepositoryException("Unable to register migration '{$class}', migration already exists");
            }
        }

        if (empty($body)) {
            throw new RepositoryException("Empty body of '{$class}'");
        }

        $filename = $this->fileService->generateClassName();
        $body = $this->fileService->replaceCycleMigrationClassName($filename, $body);
        $this->fileService->createMigrationFile($filename, $body);

        return $filename;
    }

    /**
     * Internal method to fetch all migration filenames.
     */
    private function getFiles(): \Generator
    {
        foreach ($this->fileService->getFiles() as $filename) {
            $reflection = new ReflectionFile($filename);

            $fileInfo = pathinfo($reflection->getFilename());
            $timestampFromName = str_replace('Migration', '', $fileInfo['filename']);

            $created = \DateTimeImmutable::createFromFormat('U', $timestampFromName);
            if ($created === false) {
                throw new RepositoryException("Invalid migration filename '{$filename}' - corrupted date format");
            }

            yield [
                'filename' => $filename,
                'class' => $reflection->getClasses()[0],
                'created' => $created,
                'chunk' => 0,
                'name' => $fileInfo['filename'],
            ];
        }
    }
}
