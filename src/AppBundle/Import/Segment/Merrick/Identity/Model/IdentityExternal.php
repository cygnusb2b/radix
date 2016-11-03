<?php

namespace AppBundle\Import\Segment\Merrick\Identity\Model;

use AppBundle\Import\Segment\Merrick\Identity;

class IdentityExternal extends Identity
{
    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        return 'merrick_customer_model_identity_external';
    }

    /**
     * {@inheritdoc}
     */
    protected function formatModel(array $doc)
    {
        $transformer = new Transformer\IdentityExternal('omeda', $this->getOmedaBrandKey());
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
                ['pwd'  => ['$eq'      => '']]
            ],
            'origin'  => 'link_tracking'
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

    private function getOmedaBrandKey()
    {
        $store = $this->getPersister()->getStorageEngine();
        $integration = $store->findQuery('integration-service', ['_type' => 'integration-service-omeda'])->getSingleResult();
        if (null !== $integration) {
            return $integration->get('brandKey');
        }
        throw new \RuntimeException('Unable to find an omeda integration service!');
    }
}
