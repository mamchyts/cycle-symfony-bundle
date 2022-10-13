<?php

declare(strict_types=1);

namespace Cycle\SymfonyBundle\Service;

use Symfony\Component\Filesystem\Filesystem;

class MigrationTemplateService
{
    public function __construct(
        private Filesystem $filesystem
    ) {
    }

    public function generateClassName(): string
    {
        return 'Migration' . time();
    }

    public function getEmptyMigration(string $className): string
    {
        return $this->getMigration('App\Migration', $className, '', '');
    }

    public function getMigration(string $namespace, string $className, string $up, string $down): string
    {
        return strtr(
            $this->getMigrationTemplate(),
            [
                '<namespace>' => $namespace,
                '<className>' => $className,
                '<up>' => $up,
                '<down>' => $down,
            ]
        );
    }

    public function createMigrationFile(string $dir, string $className, string $content): string
    {
        $subDir = date('Y-m');
        $filePath = $dir . '/' . $subDir . '/' . $className . '.php';

        // create sub dir if needed
        if (!$this->filesystem->exists(\dirname($filePath))) {
            $this->filesystem->mkdir(\dirname($filePath));
        }

        file_put_contents($filePath, $content);

        return $filePath;
    }

    private function getMigrationTemplate(): string
    {
        return <<<'TEMPLATE'
            <?php

            declare(strict_types=1);

            namespace <namespace>;

            use Cycle\Migrations\Migration;

            /**
             * Auto-generated migration
             */
            final class <className> extends Migration
            {
                /**
                 * Create tables, add columns or insert data here
                 */
                public function up(): void
                {
                    // this up() migration
            <up>
                }

                /**
                 * Drop created, columns and etc here
                 */
                public function down(): void
                {
                    // this down() migration
            <down>
                }
            }

            TEMPLATE;
    }
}
