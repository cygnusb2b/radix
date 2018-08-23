<?php

namespace AppBundle\Import\Segment\Merrick;

use AppBundle\Import\Segment\Merrick;

abstract class Identity extends Merrick
{
    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return $this->source->count($this->getCollection(), $this->getCriteria());
    }

    /**
     * {@inheritdoc}
     */
    protected function getCollection()
    {
        return 'users_v2';
    }

    /**
     * {@inheritdoc}
     */
    protected function getCriteria()
    {
        return ['site' => $this->importer->getDomain()];
    }

    protected function getPushIntegration()
    {
        $store = $this->getPersister()->getStorageEngine();
        $integration = $store->findQuery('integration-service', ['_type' => 'integration-service-omeda'])->getSingleResult();
        if (null !== $integration) {

            $push = $store->findQuery('integration-account-push', ['service.id' => $integration->getId()])->getSingleResult();
            if (null !== $push) {
                return $push->getId();
            }
            throw new \RuntimeException('Unable to find an omeda push integration ');
        }
        throw new \RuntimeException('Unable to find an omeda integration service!');
    }
}
