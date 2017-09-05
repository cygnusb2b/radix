<?php

namespace AppBundle\Import\Segment\Merrick\Identity;

use AppBundle\Import\Segment\Merrick\Identity;

class Internal extends Identity
{
    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        return 'merrick_customer_identity_internal';
    }

    /**
     * {@inheritdoc}
     */
    protected function formatModel(array $doc)
    {
        $transformer = new Transformer\Account('omeda', $this->getPushIntegration());
        return $transformer->toApp($doc);
    }

    /**
     * {@inheritdoc}
     */
    protected function getCriteria()
    {
        $criteria = [
            '$and' => [
                [
                    '$or'   => [
                        ['pwd'  => ['$exists'  => false]],
                        ['pwd'  => ['$eq'      => '']]
                    ]
                ],
                [
                    '$or'   => [
                        ['origin'  => ['$exists'  => false]],
                        ['origin'  => ['$ne'      => 'link_tracking']]
                    ]
                ]
            ]
        ];
        return array_merge(parent::getCriteria(), $criteria);
    }

    /**
     * {@inheritdoc}
     */
    protected function getModelType()
    {
        return 'identity-internal';
    }
}
