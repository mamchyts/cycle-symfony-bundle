<?php

declare(strict_types=1);

namespace Cycle\SymfonyBundle\Service;

use Cycle\Migrations\Config\MigrationConfig;

class MigrationService
{
    public function __construct(
        private ConfigService $configService,
    ) {
    }

    public function getMigrationConfig(): MigrationConfig
    {
        $cycleConfig = $this->configService->getConfigs();

        return new MigrationConfig([
            'directory' => $cycleConfig['migration']['directory'],
            'table' => $cycleConfig['migration']['table'],
            'safe' => $cycleConfig['migration']['safe'],
        ]);
    }
}
