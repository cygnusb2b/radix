<?php

namespace AppBundle\Integrations;

use As3\Modlr\Store\Store;

class IntegrationManager
{
    /**
     * @var ClientInterface[]
     */
    private $clients = [];

    /**
     * @var HandlerInterface[]
     */
    private $handlers = [];

    /**
     * @var Store
     */
    private $store;

    /**
     * @param   Store    $store
     */
    public function __construct(Store $store)
    {
        $this->store = $store;
    }

    /**
     * @param   ClientInteface  $client
     * @return  self
     */
    public function addClient(ClientInterface $client)
    {
        $this->clients[$client->getKey()] = $client;
        return $this;
    }

    /**
     * @param   HandlerInterface  $handler
     * @return  self
     */
    public function addHandler(HandlerInterface $handler)
    {
        $this->handlers[$handler->getKey()] = $handler;
        return $this;
    }

    /**
     * @param   string  $key
     * @return  ClientInterface|null
     */
    public function getClientFor($key)
    {
        if (isset($this->clients[$key])) {
            return $this->clients[$key];
        }
    }

    /**
     * @param   string  $key
     * @return  HandlerInterface|null
     */
    public function getHandlerFor($key)
    {
        if (isset($this->handlers[$key])) {
            return $this->handlers[$key];
        }
    }

    /**
     * @return  Store
     */
    public function getStore()
    {
        return $this->store;
    }

    /**
     * @param   string      $key
     * @param   string|null $idetifier
     */
    public function runFor($key, $identifier = null)
    {
        $handler = $this->getHandlerFor($key);
        if (null === $handler) {
            throw new \InvalidArgumentException(sprintf('No integration handler found for action "%s"', $key));
        }
        // @todo Will likely want to register callbacks for certain handler events (like start, complete, error, etc).
        $handler->setManager($this);

        if (empty($identifier)) {
            $handler->run();
        } else {
            $handler->runFor($identifier);
        }
        return $this;
    }
}
