<?php

namespace AppBundle\Import\Segment\Merrick\Customer\Model;

use AppBundle\Import\Segment\Merrick\Customer;

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
        $transformer->define('primaryEmail', 'email', 'strtolower');
        $transformer->define('legacy.external', 'origin', function($value) {
            return 'link_tracking' === $value;
        });
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

    /**
     * {@inheritdoc}
     */
    protected function getModelType()
    {
        return 'customer-identity';
    }
}
