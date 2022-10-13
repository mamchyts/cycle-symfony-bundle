<?php

declare(strict_types=1);

namespace Cycle\SymfonyBundle\Service;

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
}
