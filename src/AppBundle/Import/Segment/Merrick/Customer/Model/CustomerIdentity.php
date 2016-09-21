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
        $transformer = new Transformer\Customer();
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
            ]
        ];
        return array_merge(parent::getCriteria(), $criteria);
    }

    protected function getModelType()
    {
        return 'customer-identity';
    }
}
