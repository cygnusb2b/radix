<?php

namespace AppBundle\Import\Segment\Merrick\Identity;

use AppBundle\Import\Segment\Merrick\Identity;

/**
 * @todo: Skip duplicate key errors
 */

class External extends Identity
{
    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        return 'merrick_customer_identity_external';
    }

    /**
     * {@inheritdoc}
     */
    protected function formatModel(array $doc)
    {
        $transformer = new Transformer\External('omeda', $this->getOmedaBrandKey());
        return $transformer->toApp($doc);
    }

    /**
     * {@inheritdoc}
     */
    protected function getCriteria()
    {
        $criteria = [
            '$or'   => [
                ['pwd'  => ['$exists'  => false]],
                ['pwd'  => ['$in'      => [null, '']]]
            ],
            'origin'  => 'link_tracking',
            '$and'  => [
                ['omeda_id'  => ['$exists' => true]],
                ['omeda_id'  => ['$nin' => [null, '']]],
            ]
        ];
        return array_merge(parent::getCriteria(), $criteria);
    }

    /**
     * {@inheritdoc}
     */
    protected function getModelType()
    {
        return 'identity-external';
    }

    private $integrationKey;

    private function getOmedaBrandKey()
    {
        if (null === $this->integrationKey) {
            $store = $this->getPersister()->getStorageEngine();
            $integration = $store->findQuery('integration-service', ['_type' => 'integration-service-omeda'])->getSingleResult();
            if (null === $integration) {
                throw new \RuntimeException('Unable to find an omeda integration service!');
            }
            $this->integrationKey = $integration->get('brandKey');
        }
        return $this->integrationKey;
    }
}
