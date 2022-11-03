<?php

declare(strict_types=1);

namespace Cycle\SymfonyBundle\Migration;

use Cycle\SymfonyBundle\Service\ConfigService;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class FileService
{
    public function __construct(
        private ConfigService $configService,
        private Filesystem $filesystem,
    ) {
    }

    public function generateClassName(): string
    {
        return 'Migration' . number_format(microtime(true) * 1000000, 0, '.', '');
    }

    public function getEmptyMigration(string $className, string $database): string
    {
        return $this->getMigration($className, $database, '', '');
    }

    public function createMigrationFile(string $className, string $content): string
    {
        $subDir = date('Y-m');
        $filePath = $this->configService->getMigrationDirectory() . '/' . $subDir . '/' . $className . '.php';

        // create sub dir if needed
        if (!$this->filesystem->exists(\dirname($filePath))) {
            $this->filesystem->mkdir(\dirname($filePath));
        }

        file_put_contents($filePath, $content);

        return $filePath;
    }

    public function getFiles(): array
    {
        $finder = (new Finder())
            ->files()
            ->in($this->configService->getMigrationDirectory());

        $files = [];
        foreach ($finder as $file) {
            $files[] = $file->getRealPath();
        }

        return $files;
    }

    public function replaceCycleMigrationClassName(string $newClassName, string $body): string
    {
        /** @var string */
        $body = preg_replace(
            '/class (.*) extends Migration/i',
            'class ' . $newClassName . ' extends Migration',
            $body,
        );

        return $body;
    }

    private function getMigration(string $className, string $database, string $up, string $down): string
    {
        return strtr(
            $this->getMigrationTemplate(),
            [
                '<className>' => $className,
                '<database>' => $database,
                '<up>' => $up,
                '<down>' => $down,
            ],
        );
    }

    private function getMigrationTemplate(): string
    {
        return <<<'TEMPLATE'
            <?php

            declare(strict_types=1);

            namespace Migration;

            use Cycle\Migrations\Migration;

            class <className> extends Migration
            {
                protected const DATABASE = '<database>';

                public function up(): void
                {
                    // this up() migration
            <up>
                }

                public function down(): void
                {
                    // this down() migration
            <down>
                }
            }

            TEMPLATE;
    }
}
