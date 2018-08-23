<?php

namespace AppBundle\Integration\Handler;

use AppBundle\Integration\ServiceInterface;

interface HandlerInterface
{
    /**
     * Sets the service to the integration handler.
     *
     * @param   ServiceInterface    $service
     * @return  self
     * @throws  \InvalidArgumentException   If the service is not valid for the handler.
     */
    public function setService(ServiceInterface $service);

    /**
     * Deterimes if the provided service class name is supported by this handler.
     *
     * @param   string  $className
     * @return  bool
     */
    public function supportsServiceClass($className);
}
