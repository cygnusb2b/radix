<?php

namespace AppBundle\Core;

use Redis;
use Snc\RedisBundle\Client\Phpredis\Client;

class RedisCacheManager
{
    /**
     * @var Redis[]
     */
    private $clients = [];

    /**
     * Adds a Redis client.
     *
     * @param   string  $name
     * @param   Redis   $client
     */
    public function addClient($name, Redis $client)
    {
        $this->clients[$name] = $client;
        return $this;
    }

    /**
     * Appends the provided application prefix to all registered clients.
     *
     * @param   string  $prefix
     * @return  self
     */
    public function appendApplicationPrefix($prefix)
    {
        foreach ($this->getClients() as $client) {
            $current = $client->getOption(Redis::OPT_PREFIX);
            if (!empty($current)) {
                $prefix = sprintf('%s:%s', $prefix, $current);
            }
            $client->setOption(Redis::OPT_PREFIX, $prefix);
        }
        return $this;
    }

    /**
     * Gets all registered Redis clients.
     *
     * @return  Redis[]
     */
    public function getClients()
    {
        return $this->clients;
    }
}
