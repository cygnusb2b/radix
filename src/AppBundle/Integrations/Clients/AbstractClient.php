<?php

namespace AppBundle\Integrations\Clients;

use AppBundle\Integrations\ClientInterface;
use As3\Parameters\DefinedParameters as Parameters;
use As3\Parameters\Definitions;

abstract class AbstractClient implements ClientInterface
{
    /**
     * @var Parameters
     */
    protected $parameters;

    /**
     * Constructor.
     *
     * @param   array|null $parameters
     */
    public function __construct(array $parameters = null)
    {
        if (null !== $parameters) {
            $this->configure($parameters);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configure(array $parameters)
    {
        $this->parameters = $this->createParameterInstance($parameters);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function createParameterInstance(array $parameters)
    {
        return new Parameters($this->getParameterDefinitions(), $parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigParameters()
    {
        return $this->parameters;
    }

    /**
     * {@inheritdoc}
     */
    abstract public function getParameterDefinitions();

    /**
     * {@inheritdoc}
     */
    public function hasSupportFor($integrationType)
    {
        return $this->support->hasSupportFor($integrationType);
    }

    /**
     * {@inheritdoc}
     */
    public function hasValidConfig()
    {
        return null !== $this->parameters && $this->parameters->valid();
    }

    /**
     * Validates that this client can perform an integration execution.
     *
     * @throws  \RuntimeException
     */
    protected function validateCanExecute()
    {
        if (false === $this->hasValidConfig()) {
            throw new \RuntimeException('The client is not properly configured. Unable to start the requested integration task.');
        }
    }
}
