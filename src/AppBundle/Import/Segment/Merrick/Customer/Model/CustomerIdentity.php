<?php

namespace AppBundle\Import\Segment\Merrick\Customer\Model;

class CustomerIdentity extends Customer
{
    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        return 'merrick_customer_model_customer_identity';
    }

    /**
     * {@inheritdoc}
     */
    protected function formatModel(array $doc)
    {
        $kv = [
            'legacy'    => [
                'id'        => (string) $doc['_id'],
                'source'    => 'users_v2',
            ],
        ];

        $transformer = new Transformer\CustomerAccount();
        return array_merge($kv, $transformer->toApp($doc));
    }

    /**
     * {@inheritdoc}
     */
    protected function getCriteria()
    {
        $criteria = [
            'pwd'   => [
                '$or'   => [
                    ['$exists'  => false],
                    ['$eq'      => '']
                ]
            ]
        ];
        return array_merge(parent::getCriteria(), $criteria);
    }

    protected function getModelType()
    {
        return 'customer-identity';
    }
}
