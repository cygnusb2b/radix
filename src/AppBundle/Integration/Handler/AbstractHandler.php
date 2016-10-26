<?php

namespace AppBundle\Integration\Handler;

use AppBundle\Integration\ServiceInterface;

abstract class AbstractHandler implements HandlerInterface
{
    /**
     * @var ServiceInterface
     */
    protected $service;

    /**
     * {@inheritdoc}
     */
    final public function setService(ServiceInterface $service)
    {
        $class = get_class($service);
        if (false === $this->supportsServiceClass($class)) {
            throw new \InvalidArgumentException(sprintf('The integration service class `%s` is not supported by this handler.', $class));
        }
        $this->service = $service;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    abstract public function supportsServiceClass($className);
}
