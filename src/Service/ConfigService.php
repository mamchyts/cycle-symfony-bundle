<?php

declare(strict_types=1);

namespace Cycle\SymfonyBundle\Service;

use Cycle\SymfonyBundle\Exception\AbstractException;
use Cycle\SymfonyBundle\SymfonyBundle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ConfigService
{
    private array $parameters;

    public function __construct(
        ParameterBagInterface $parameterBag,
    ) {
        /** @var array */
        $parameters = $parameterBag->get(SymfonyBundle::CYCLE_PARAMETER_CONFIG);

        $this->parameters = $parameters;
    }

    public function getConfigs(): array
    {
        return $this->parameters;
    }

    /** @throws AbstractException */
    public function getDefaultConnection(): string
    {
        foreach ($this->parameters['databases'] as $database) {
            if ($database['default'] === true) {
                return $database['connection'];
            }
        }

        throw new AbstractException('Can not find default connection name');
    }

    public function getMigrationDirectory(): string
    {
        return $this->parameters['migration']['directory'];
    }
}
